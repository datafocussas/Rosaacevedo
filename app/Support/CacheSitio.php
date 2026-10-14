<?php

namespace App\Support;

use App\Services\Ajustes;
use App\Services\TextosLegales;
use Illuminate\Support\Facades\Cache;
use Spatie\ResponseCache\Facades\ResponseCache;

/** Limpia las cachés del sitio público cuando cambia contenido o configuración. */
final class CacheSitio
{
    public static function limpiar(): void
    {
        foreach (['layout-sitio', 'ajustes', 'ab-variantes', 'api-barrios'] as $clave) {
            Cache::forget($clave);
        }

        app(Ajustes::class)->olvidar();
        app(TextosLegales::class)->olvidar();

        try {
            ResponseCache::clear();
        } catch (\Throwable) {
            // Sin caché de respuestas (pruebas): nada que limpiar.
        }
    }
}
