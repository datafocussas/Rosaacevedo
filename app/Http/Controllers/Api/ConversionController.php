<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AsignarVariante;
use App\Models\Interaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Eventos de analítica propios para el reporte A/B (clic en WhatsApp, compartir…). Sin datos personales. */
class ConversionController extends Controller
{
    public function __invoke(Request $request)
    {
        $datos = json_decode($request->getContent(), true) ?: $request->all();
        $tipo = $datos['tipo'] ?? null;

        // Los pasos del registro ya quedan como interacción en el servidor; aquí solo los eventos sin envío.
        if (in_array($tipo, ['whatsapp_clic', 'compartir'], true)) {
            [$visitante] = explode('.', (string) $request->cookie(AsignarVariante::COOKIE), 2);
            Interaccion::query()->create([
                'tipo' => $tipo,
                'visitante_id' => Str::isUuid($visitante) ? $visitante : null,
                'variante' => isset($datos['variante']) ? Str::limit((string) $datos['variante'], 10, '') : null,
                'pagina' => isset($datos['pagina']) ? Str::limit((string) $datos['pagina'], 255, '') : null,
            ]);
        }

        return response()->noContent();
    }
}
