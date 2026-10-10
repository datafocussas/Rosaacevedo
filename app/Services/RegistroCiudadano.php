<?php

namespace App\Services;

use App\Models\Ciudadano;
use App\Models\Interaccion;
use App\Models\TerritorioBarrio;
use App\Models\Voluntariado;
use App\Services\Crm\Outbox;
use App\Services\Crm\Payloads;
use App\Support\Celular;
use App\Support\Origen;
use App\Support\TokenRegistro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Captación progresiva en 3 pasos (RF-01 a RF-04). Un ciudadano = un registro:
 * si el celular ya existe se completan los campos vacíos y se suma la interacción.
 */
class RegistroCiudadano
{
    public function __construct(private Consentimientos $consentimientos) {}

    /**
     * Paso 1: nombre, celular y autorizaciones.
     *
     * @return array{ciudadano: Ciudadano, token: string, codigo: string, nuevo: bool}
     */
    public function paso1(array $datos, Origen $origen, Request $request, string $formulario = 'registro_p1'): array
    {
        $e164 = Celular::normalizar($datos['celular'] ?? null);
        if (! $e164) {
            throw ValidationException::withMessages(['celular' => 'Escribe los 10 dígitos de tu celular.']);
        }

        $this->limitarPorCelular($e164);

        $resultado = DB::transaction(function () use ($datos, $e164, $origen, $request, $formulario) {
            [$ciudadano, $nuevo] = $this->encontrarOCrear($e164, $datos['nombre'], $origen);

            $this->consentimientos->registrar($ciudadano, 'autorizacion_general', true, $formulario, $request);
            if (! empty($datos['consent_whatsapp'])) {
                $this->consentimientos->registrar($ciudadano, 'whatsapp', true, $formulario, $request);
            }

            Outbox::registrar('ciudadano', $ciudadano->id, $nuevo ? 'creado' : 'actualizado', Payloads::ciudadano($ciudadano->fresh(), $nuevo ? 'creado' : 'actualizado'));
            $this->interaccion($ciudadano, 'registro_paso_1', $origen);

            return [$ciudadano, $nuevo];
        });

        [$ciudadano, $nuevo] = $resultado;

        return [
            'ciudadano' => $ciudadano,
            'token' => TokenRegistro::emitir($ciudadano->uuid, 2),
            'codigo' => $ciudadano->codigo(),
            'nuevo' => $nuevo,
        ];
    }

    /**
     * Pasos 2 y 3 con el token firmado. Devuelve el ciudadano y el token del siguiente paso.
     *
     * @return array{ciudadano: Ciudadano, paso: int, token: ?string}
     */
    public function completar(string $token, array $datos, Origen $origen, Request $request): array
    {
        // La comuna es obligatoria en el paso 2 (con o sin catálogo de barrios). Se valida antes de consumir
        // el token para que un error no obligue a la persona a empezar de nuevo.
        $previo = TokenRegistro::disponible($token);
        $barrio = ! empty($datos['barrio_id']) ? TerritorioBarrio::query()->find($datos['barrio_id']) : null;
        $comunaId = $barrio?->comuna_2024_id ?? ($datos['comuna_id'] ?? null);
        if ($previo && (int) $previo['p'] === 2 && ! $comunaId) {
            throw ValidationException::withMessages(['comuna_id' => 'Elige tu comuna o el corregimiento.']);
        }

        $leido = TokenRegistro::consumir($token);
        $ciudadano = $leido ? Ciudadano::query()->where('uuid', $leido['uuid'])->first() : null;

        if (! $ciudadano || $ciudadano->estado === 'retirado') {
            throw ValidationException::withMessages(['token' => 'El enlace venció o ya se usó. Vuelve a empezar el registro.']);
        }

        $paso = $leido['paso'];

        DB::transaction(function () use ($ciudadano, $paso, $datos, $origen, $request, $barrio, $comunaId) {
            if ($paso === 2) {
                $ciudadano->email = $datos['email'] ?? $ciudadano->email;
                $ciudadano->barrio_id = $barrio?->id ?? $ciudadano->barrio_id;
                $ciudadano->comuna_id = $comunaId ?? $ciudadano->comuna_id;
            } else {
                $ciudadano->es_voluntario = true;
                Voluntariado::query()->updateOrCreate(['ciudadano_id' => $ciudadano->id], [
                    'intereses' => array_values($datos['intereses'] ?? []),
                    'disponibilidad' => $datos['disponibilidad'] ?? null,
                    // Solo si la persona lo comparte (regla 5).
                    'puesto_votacion' => $datos['puesto_votacion'] ?? null,
                ]);
                if (! empty($datos['consent_afinidad'])) {
                    $this->consentimientos->registrar($ciudadano, 'afinidad_politica', true, 'registro_p3', $request);
                }
            }

            $ciudadano->paso_alcanzado = max($ciudadano->paso_alcanzado, $paso);
            $ciudadano->save();

            Outbox::registrar('ciudadano', $ciudadano->id, 'actualizado', Payloads::ciudadano($ciudadano->fresh(), 'actualizado'));
            $this->interaccion($ciudadano, 'registro_paso_'.$paso, $origen);
        });

        return [
            'ciudadano' => $ciudadano,
            'paso' => $paso,
            'token' => $paso < 3 ? TokenRegistro::emitir($ciudadano->uuid, $paso + 1) : null,
        ];
    }

    /**
     * Ubica al ciudadano por el HMAC del celular o lo crea. Se usa también desde el buzón y la agenda.
     *
     * @return array{0: Ciudadano, 1: bool}
     */
    public function encontrarOCrear(string $e164, string $nombre, Origen $origen): array
    {
        $ciudadano = Ciudadano::query()->where('celular_hash', Celular::hmac($e164))->lockForUpdate()->first();

        if ($ciudadano) {
            if (blank($ciudadano->nombre)) {
                $ciudadano->nombre = $nombre;
            }
            if ($ciudadano->estado === 'retirado') {
                // Vuelve a autorizar: la nueva autorización queda en consentimientos.
                $ciudadano->estado = 'activo';
            }
            $ciudadano->crm_sync_estado = 'pendiente';
            $ciudadano->save();

            return [$ciudadano, false];
        }

        $ciudadano = new Ciudadano([
            'nombre' => $nombre,
            'primer_origen' => $origen->paraCiudadano() ?: null,
        ]);
        $ciudadano->asignarCelular($e164);
        $ciudadano->save();

        return [$ciudadano, true];
    }

    public function interaccion(?Ciudadano $ciudadano, string $tipo, Origen $origen): Interaccion
    {
        $interaccion = Interaccion::query()->create(['ciudadano_id' => $ciudadano?->id, 'tipo' => $tipo] + $origen->paraInteraccion());

        if ($ciudadano) {
            Outbox::registrar('interaccion', $interaccion->id, 'creado', Payloads::interaccion($interaccion));
        }

        return $interaccion;
    }

    /** RF-05: máximo 3 envíos por celular al día. */
    public function limitarPorCelular(string $e164): void
    {
        $clave = 'celular:'.Celular::hmac($e164);
        $maximo = config('rosa.limites.por_celular_dia', 3);

        if (RateLimiter::tooManyAttempts($clave, $maximo)) {
            throw ValidationException::withMessages(['celular' => 'Ya recibimos varios envíos con este celular hoy. Inténtalo mañana.']);
        }

        RateLimiter::hit($clave, 86400);
    }
}
