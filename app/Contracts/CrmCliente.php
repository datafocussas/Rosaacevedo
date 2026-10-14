<?php

namespace App\Contracts;

use App\Support\RespuestaCrm;

/**
 * Adaptador hacia el CRM de la campaña. El contrato real se ajusta cuando llegue la
 * especificación OpenAPI del CRM (`php artisan scramble:export`); el resto del sitio solo
 * conoce esta interfaz y el outbox.
 */
interface CrmCliente
{
    /**
     * Entrega una fila del outbox.
     *
     * @param  string  $entidad  ciudadano, propuesta, asistencia, interaccion…
     * @param  array  $payload  Cuerpo ya armado (ver App\Services\Crm\Payloads).
     * @param  string  $idempotencia  UUID estable de la fila del outbox.
     */
    public function enviar(string $entidad, array $payload, string $idempotencia): RespuestaCrm;
}
