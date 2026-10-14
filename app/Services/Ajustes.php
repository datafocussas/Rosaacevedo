<?php

namespace App\Services;

use App\Models\Ajuste;
use Illuminate\Support\Facades\Cache;

/**
 * Lectura de la tabla `ajustes` con caché. Valores por defecto seguros si falta una clave.
 */
class Ajustes
{
    public const MODOS = [
        'precampana' => 'Precampaña',
        'campana' => 'Campaña',
        'dia_d' => 'Día D',
    ];

    private ?array $valores = null;

    public function todos(): array
    {
        if ($this->valores !== null) {
            return $this->valores;
        }

        try {
            return $this->valores = Cache::remember('ajustes', 3600, fn () => Ajuste::query()->pluck('valor', 'clave')->all());
        } catch (\Throwable) {
            // Sin base de datos (instalación inicial): se usan los valores por defecto.
            return $this->valores = [];
        }
    }

    public function get(string $clave, mixed $defecto = null): mixed
    {
        return $this->todos()[$clave] ?? $defecto;
    }

    public function set(string $clave, mixed $valor, ?string $grupo = null): void
    {
        $actual = Ajuste::query()->find($clave);
        $antes = $actual?->valor;

        if ($actual && $antes === $valor) {
            return;
        }

        Ajuste::query()->updateOrCreate(['clave' => $clave], [
            'valor' => $valor,
            'grupo' => $grupo ?? $actual?->grupo ?? 'general',
            'updated_by' => auth()->id(),
        ]);

        // Cada cambio de configuración queda en la bitácora (sección 03).
        activity('configuracion')->causedBy(auth()->user())
            ->withProperties(['clave' => $clave, 'antes' => $antes, 'despues' => $valor])
            ->log('Cambio de configuración: '.$clave);

        $this->olvidar();
    }

    public function olvidar(): void
    {
        $this->valores = null;
        Cache::forget('ajustes');
    }

    public function modo(): string
    {
        return $this->get('modo_sitio', 'precampana');
    }

    public function enPrecampana(): bool
    {
        return $this->modo() === 'precampana';
    }

    /** Número de WhatsApp Business en formato wa.me o null si aún no está configurado. */
    public function whatsappNumero(): ?string
    {
        $numero = preg_replace('/\D+/', '', (string) $this->get('whatsapp_numero', ''));

        return strlen($numero) >= 11 ? $numero : null;
    }

    /** Enlace wa.me con el mensaje prellenado y el código de atribución (RF-06). */
    public function enlaceWhatsapp(?string $codigo = null): ?string
    {
        $numero = $this->whatsappNumero();
        if (! $numero) {
            return null;
        }

        $mensaje = (string) $this->get('whatsapp_mensaje', 'Hola, quiero saber más de Rosa. Código: {codigo}');
        $mensaje = $codigo ? str_replace('{codigo}', $codigo, $mensaje) : trim(preg_replace('/\s*C[oó]digo:\s*\{codigo\}/u', '', $mensaje));

        return 'https://wa.me/'.$numero.'?text='.rawurlencode($mensaje);
    }
}
