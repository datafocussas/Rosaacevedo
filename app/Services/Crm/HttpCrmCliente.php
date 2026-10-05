<?php

namespace App\Services\Crm;

use App\Contracts\CrmCliente;
use App\Support\RespuestaCrm;
use Illuminate\Support\Facades\Http;

/**
 * Adaptador HTTP según la propuesta de la sección 05:
 * POST {CRM_URL}/ingesta/{entidad} con X-Firma: sha256=HMAC(cuerpo, CRM_SECRETO) y X-Idempotencia.
 *
 * PENDIENTE: confirmar ruta, cuerpo y respuesta contra la especificación OpenAPI del CRM
 * (aplicativo.rosaacevedo.co/docs/api). Solo debería cambiar esta clase.
 */
class HttpCrmCliente implements CrmCliente
{
    public function enviar(string $entidad, array $payload, string $idempotencia): RespuestaCrm
    {
        $url = rtrim((string) config('rosa.crm.url'), '/');
        $secreto = (string) config('rosa.crm.secreto');

        if ($url === '' || $secreto === '') {
            return RespuestaCrm::fallo('CRM_URL o CRM_SECRETO sin configurar.');
        }

        $cuerpo = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            $respuesta = Http::timeout(config('rosa.crm.timeout', 10))
                ->acceptJson()
                ->withHeaders([
                    'X-Firma' => 'sha256='.hash_hmac('sha256', $cuerpo, $secreto),
                    'X-Idempotencia' => $idempotencia,
                ])
                ->withBody($cuerpo, 'application/json')
                ->post($url.'/ingesta/'.$entidad);
        } catch (\Throwable $e) {
            return RespuestaCrm::fallo('Sin conexión con el CRM: '.$e->getMessage());
        }

        if ($respuesta->successful()) {
            return RespuestaCrm::exito($respuesta->json('id') ?? $respuesta->json('crm_id'));
        }

        // 4xx distintos de 408/429 no se arreglan reintentando, pero se reintentan igual con
        // espera larga para no perder datos; el error queda en la bitácora del outbox.
        return RespuestaCrm::fallo('HTTP '.$respuesta->status().': '.mb_substr($respuesta->body(), 0, 300));
    }
}
