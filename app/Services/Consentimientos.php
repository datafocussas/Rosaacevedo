<?php

namespace App\Services;

use App\Models\Ciudadano;
use App\Models\Consentimiento;
use App\Models\Politica;
use Illuminate\Http\Request;
use RuntimeException;

/** Registra la evidencia de cada casilla (sección 07): versión del texto, IP, agente, formulario, URL y hora en ms. */
class Consentimientos
{
    public function registrar(Ciudadano $ciudadano, string $tipo, bool $otorgado, string $formulario, Request $request): Consentimiento
    {
        $politica = Politica::vigente($tipo);

        if (! $politica) {
            throw new RuntimeException("No hay una versión vigente del texto «{$tipo}».");
        }

        return Consentimiento::query()->create([
            'ciudadano_id' => $ciudadano->id,
            'politica_id' => $politica->id,
            'otorgado' => $otorgado,
            'formulario' => $formulario,
            'ip' => @inet_pton((string) $request->ip()) ?: inet_pton('0.0.0.0'),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            'url' => mb_substr((string) ($request->headers->get('referer') ?: $request->fullUrl()), 0, 255),
            'created_at' => now(),
        ]);
    }
}
