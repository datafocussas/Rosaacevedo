<?php

namespace App\Services\Crm;

use App\Models\CrmOutbox;
use Illuminate\Support\Str;

/**
 * Escribe en `crm_outbox`. Se llama dentro de la misma transacción que el cambio de datos,
 * para que nada se pierda aunque el CRM esté caído (RNF-04).
 */
class Outbox
{
    public static function registrar(string $entidad, int $entidadId, string $evento, array $payload): CrmOutbox
    {
        return CrmOutbox::query()->create([
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'evento' => $evento,
            'payload' => $payload,
            'idempotencia' => (string) Str::uuid(),
            'estado' => 'pendiente',
            'proximo_intento' => now(),
        ]);
    }
}
