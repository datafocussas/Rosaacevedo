<?php

namespace App\Support\Cache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\Hasher\DefaultHasher;

/** Ignora utm_*, fbclid y similares para que las visitas desde pauta compartan la página en caché. */
class HasherSitio extends DefaultHasher
{
    private const IGNORAR = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'gclid', 'ttclid', 'igshid'];

    protected function getNormalizedRequestUri(Request $request): string
    {
        $consulta = array_diff_key($request->query(), array_flip(self::IGNORAR));
        ksort($consulta);
        $consulta = http_build_query($consulta);

        return $request->getBaseUrl().$request->getPathInfo().($consulta ? '?'.$consulta : '');
    }
}
