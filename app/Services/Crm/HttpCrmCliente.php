<?php

namespace App\Services\Crm;

use App\Contracts\CrmCliente;
use App\Support\RespuestaCrm;
use Illuminate\Support\Facades\Http;

/**
 * Adaptador HTTP hacia el CRM.
 *
 * 1. Conexión del panel (Sitio → Conexión con el CRM): POST a la URL configurada para cada tipo de dato,
 *    con la API key (Bearer o encabezado propio), X-Idempotencia y solo los campos configurados.
 * 2. Si el panel no está activo, la del .env (sección 05): POST {CRM_URL}/ingesta/{entidad} firmado con
 *    X-Firma: sha256=HMAC(cuerpo, CRM_SECRETO).
 *
 * La URL y los campos los define la campaña según la documentación del CRM: el sitio no supone rutas.
 */
class HttpCrmCliente implements CrmCliente
{
    public function __construct(private ?CrmConexion $conexion = null)
    {
        $this->conexion ??= app(CrmConexion::class);
    }

    public function enviar(string $entidad, array $payload, string $idempotencia): RespuestaCrm
    {
        if ($this->conexion->activa()) {
            $url = $this->conexion->url($entidad);
            if (blank($url)) {
                return RespuestaCrm::fallo('Sin URL configurada en el panel para «'.$entidad.'».');
            }

            return $this->post($url, $this->conexion->cuerpo($entidad, $payload), $this->conexion->encabezados() + ['X-Idempotencia' => $idempotencia]);
        }

        $url = rtrim((string) config('rosa.crm.url'), '/');
        $secreto = (string) config('rosa.crm.secreto');

        if ($url === '' || $secreto === '') {
            return RespuestaCrm::fallo('CRM sin configurar: actívalo en Sitio → Conexión con el CRM.');
        }

        $cuerpo = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this->post($url.'/ingesta/'.$entidad, $payload, [
            'X-Firma' => 'sha256='.hash_hmac('sha256', $cuerpo, $secreto),
            'X-Idempotencia' => $idempotencia,
        ]);
    }

    private function post(string $url, array $cuerpo, array $encabezados): RespuestaCrm
    {
        try {
            $respuesta = Http::timeout(config('rosa.crm.timeout', 10))
                ->acceptJson()
                ->withHeaders($encabezados)
                ->withBody(json_encode($cuerpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'application/json')
                ->post($url);
        } catch (\Throwable $e) {
            return RespuestaCrm::fallo('Sin conexión con el CRM: '.$e->getMessage());
        }

        if ($respuesta->successful()) {
            $id = $respuesta->json('id') ?? $respuesta->json('crm_id') ?? $respuesta->json('data.id');

            return RespuestaCrm::exito($id !== null ? (string) $id : null);
        }

        // Se reintenta siempre con espera creciente para no perder datos; el error queda en el outbox.
        return RespuestaCrm::fallo('HTTP '.$respuesta->status().': '.mb_substr($respuesta->body(), 0, 300));
    }
}
