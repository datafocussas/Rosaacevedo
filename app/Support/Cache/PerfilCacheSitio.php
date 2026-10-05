<?php

namespace App\Support\Cache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Symfony\Component\HttpFoundation\Response;

/**
 * Caché de páginas públicas completas. No guarda páginas con mensajes de sesión (errores,
 * datos viejos de formulario, avisos), ni del panel, ni vistas previas. Separa por variante A/B.
 */
class PerfilCacheSitio extends CacheAllSuccessfulGetRequests
{
    public function shouldCacheRequest(Request $request): bool
    {
        if ($request->is('admin', 'admin/*', 'livewire/*', 'vista-previa/*', 'q/*', 'sumate/*', 'mis-datos*', 'api/*')) {
            return false;
        }

        if ($request->user()) {
            return false;
        }

        if ($request->hasSession()) {
            $sesion = $request->session();
            if ($sesion->has('errors') || $sesion->hasOldInput() || $sesion->has('estado') || $sesion->has('_flash.new') && $sesion->get('_flash.new') !== []) {
                return false;
            }
        }

        // Las URL con utm_* comparten la entrada de caché (ver HasherSitio).
        return parent::shouldCacheRequest($request);
    }

    public function shouldCacheResponse(Response $response): bool
    {
        return $response->isSuccessful() && parent::shouldCacheResponse($response);
    }

    public function useCacheNameSuffix(Request $request): string
    {
        return 'v'.($request->attributes->get('variante') ?? '0');
    }
}
