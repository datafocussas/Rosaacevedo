<?php

namespace App\Http\Middleware;

use App\Models\Banner;
use App\Models\Interaccion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prueba A/B de la página de entrada (RF-08): asigna una variante por visitante con una cookie
 * técnica de primera parte y registra la asignación para contar visitantes por variante.
 * Va antes de la caché de respuestas: la caché se separa por variante.
 */
class AsignarVariante
{
    public const COOKIE = 'ra_vis';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || $request->is('admin', 'admin/*', 'livewire/*')) {
            return $next($request);
        }

        $variantes = self::variantesActivas();
        [$visitante, $variante] = array_pad(explode('.', (string) $request->cookie(self::COOKIE), 2), 2, null);

        $nuevo = ! Str::isUuid((string) $visitante);
        if ($nuevo) {
            $visitante = (string) Str::uuid();
        }

        $asignar = $variantes !== [] && ! in_array($variante, $variantes, true);
        if ($asignar) {
            $variante = $variantes[array_rand($variantes)];
        } elseif ($variantes === []) {
            $variante = null;
        }

        $request->attributes->set('variante', $variante);
        $request->attributes->set('visitante_id', $visitante);

        if ($asignar) {
            Interaccion::query()->create([
                'visitante_id' => $visitante,
                'tipo' => 'ab_visita',
                'variante' => $variante,
                'pagina' => '/'.ltrim($request->path(), '/'),
            ]);
        }

        $response = $next($request);

        if ($nuevo || $asignar) {
            Cookie::queue(Cookie::make(self::COOKIE, $visitante.'.'.$variante, 60 * 24 * 182, '/', null, $request->isSecure(), true, false, 'Lax'));
        }

        return $response;
    }

    /** Variantes con banners vigentes en la página de inicio. */
    public static function variantesActivas(): array
    {
        return Cache::remember('ab-variantes', 300, fn () => Banner::query()->vigentes()
            ->where('pagina', 'inicio')->whereNotNull('variante')
            ->distinct()->orderBy('variante')->pluck('variante')->all());
    }
}
