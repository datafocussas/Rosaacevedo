<?php

namespace App\Http\Controllers;

use App\Models\EnlaceCorto;
use App\Models\Interaccion;
use Illuminate\Http\Request;

/** /q/{codigo}: redirige al destino con utm_* y registra el clic y el origen (RF-09). */
class EnlaceCortoController extends Controller
{
    public function __invoke(Request $request, string $codigo)
    {
        $enlace = EnlaceCorto::query()->where('codigo', $codigo)->where('activo', true)->first();

        if (! $enlace) {
            return redirect()->route('inicio');
        }

        $enlace->increment('clics');

        // El código queda en la sesión: los registros posteriores se atribuyen a esta pieza.
        $request->session()->put('origen', array_filter([
            'codigo_q' => $enlace->codigo,
            'utm_source' => 'qr',
            'utm_medium' => 'impreso',
            'utm_campaign' => $enlace->codigo,
            'utm_content' => $enlace->pieza,
            'comuna_pagina' => $enlace->comuna_id,
            'evento_id' => $enlace->evento_id,
        ]) + (array) $request->session()->get('origen', []));

        Interaccion::query()->create([
            'tipo' => 'qr',
            'visitante_id' => $request->attributes->get('visitante_id'),
            'variante' => $request->attributes->get('variante'),
            'pagina' => '/q/'.$enlace->codigo,
            'comuna_pagina_id' => $enlace->comuna_id,
            'codigo_q' => $enlace->codigo,
            'utm_source' => 'qr',
            'utm_medium' => 'impreso',
            'utm_campaign' => $enlace->codigo,
            'referrer' => mb_substr((string) $request->headers->get('referer'), 0, 255) ?: null,
        ]);

        $destino = $enlace->destino;
        $separador = str_contains($destino, '?') ? '&' : '?';
        $utm = http_build_query(['utm_source' => 'qr', 'utm_medium' => 'impreso', 'utm_campaign' => $enlace->codigo] + ($enlace->pieza ? ['utm_content' => $enlace->pieza] : []));

        return redirect()->away($destino.$separador.$utm, 302)->header('Cache-Control', 'no-store');
    }
}
