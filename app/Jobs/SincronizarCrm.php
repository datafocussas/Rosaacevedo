<?php

namespace App\Jobs;

use App\Contracts\CrmCliente;
use App\Models\Ciudadano;
use App\Models\CrmOutbox;
use App\Models\User;
use App\Notifications\FalloSincronizacionCrm;
use App\Services\Crm\CrmConexion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Toma las filas pendientes del outbox y las entrega al CRM (RF-07, sección 05).
 * Reintentos con espera exponencial: 1, 5, 15 y 60 minutos; después, cada 6 horas.
 * Alerta por correo al Administrador en el 5.º fallo.
 */
class SincronizarCrm implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const ESPERAS_MINUTOS = [1, 5, 15, 60];

    public const ESPERA_LARGA_MINUTOS = 360;

    public int $uniqueFor = 120;

    public int $timeout = 50;

    public function handle(CrmCliente $crm): void
    {
        if (! CrmConexion::habilitada()) {
            return; // Sin CRM configurado, las filas esperan pendientes.
        }

        // Con la conexión del panel solo viajan los tipos de dato que tienen URL; el resto espera.
        $conexion = app(CrmConexion::class);
        $entidades = $conexion->activa() ? $conexion->entidades() : null;

        $filas = CrmOutbox::query()
            ->whereIn('estado', ['pendiente', 'error'])
            ->when($entidades !== null, fn ($q) => $q->whereIn('entidad', $entidades))
            ->where(fn ($q) => $q->whereNull('proximo_intento')->orWhere('proximo_intento', '<=', now()))
            ->orderBy('id')
            ->limit(config('rosa.crm.lote', 50))
            ->get();

        foreach ($filas as $fila) {
            $this->procesar($crm, $fila);
        }
    }

    public function procesar(CrmCliente $crm, CrmOutbox $fila): void
    {
        $respuesta = $crm->enviar($fila->entidad, $fila->payload, $fila->idempotencia);

        if ($respuesta->ok) {
            $fila->update(['estado' => 'enviado', 'ultimo_error' => null, 'proximo_intento' => null, 'intentos' => $fila->intentos + 1]);

            if ($fila->entidad === 'ciudadano') {
                Ciudadano::query()->whereKey($fila->entidad_id)->update(array_filter([
                    'crm_id' => $respuesta->crmId,
                    'crm_sync_estado' => 'sincronizado',
                    'crm_sync_en' => now(),
                ], fn ($v) => $v !== null));
            }

            return;
        }

        $intentos = $fila->intentos + 1;
        $espera = self::ESPERAS_MINUTOS[$intentos - 1] ?? self::ESPERA_LARGA_MINUTOS;

        $fila->update([
            'estado' => 'error',
            'intentos' => min($intentos, 255),
            'ultimo_error' => mb_substr((string) $respuesta->error, 0, 500),
            'proximo_intento' => now()->addMinutes($espera),
        ]);

        if ($fila->entidad === 'ciudadano') {
            Ciudadano::query()->whereKey($fila->entidad_id)->update(['crm_sync_estado' => 'error']);
        }

        if ($intentos === 5) {
            $this->alertar($fila);
        }
    }

    private function alertar(CrmOutbox $fila): void
    {
        $administradores = User::role('administrador')->where('activo', true)->get();

        if ($administradores->isNotEmpty()) {
            Notification::send($administradores, new FalloSincronizacionCrm($fila));
        } elseif ($correo = config('rosa.crm.alerta_email')) {
            Notification::route('mail', $correo)->notify(new FalloSincronizacionCrm($fila));
        }
    }
}
