<?php

namespace App\Models\Concerns;

use App\Support\CacheSitio;

/** Al guardar o borrar, la caché del sitio público se limpia (flujo editorial, sección 03). */
trait LimpiaCacheSitio
{
    public static function bootLimpiaCacheSitio(): void
    {
        static::saved(fn () => CacheSitio::limpiar());
        static::deleted(fn () => CacheSitio::limpiar());
    }
}
