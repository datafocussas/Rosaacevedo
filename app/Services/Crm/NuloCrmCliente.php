<?php

namespace App\Services\Crm;

use App\Contracts\CrmCliente;
use App\Support\RespuestaCrm;

/** Mientras no exista el contrato del CRM: no envía nada y deja las filas pendientes. */
class NuloCrmCliente implements CrmCliente
{
    public function enviar(string $entidad, array $payload, string $idempotencia): RespuestaCrm
    {
        return RespuestaCrm::fallo('CRM sin configurar (CRM_DRIVER=nulo).');
    }
}
