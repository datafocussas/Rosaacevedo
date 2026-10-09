<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Imágenes de medialibrary para las vistas (diseño v2, §8.1 y §8.2): URL de la conversión WebP si ya existe y
 * punto focal (x %, y %) guardado como propiedad de la imagen, sin columnas nuevas.
 */
final class Medios
{
    public const FOCO_POR_DEFECTO = [62, 18];

    public static function url(?Media $media, string $conversion = 'w1200'): ?string
    {
        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion($conversion) ? $media->getUrl($conversion) : $media->getUrl();
    }

    /** «62% 18%» para object-position. */
    public static function foco(?Media $media, ?array $porDefecto = null): string
    {
        [$x, $y] = $porDefecto ?? self::FOCO_POR_DEFECTO;
        $foco = $media?->getCustomProperty('foco');

        if (is_array($foco)) {
            $x = is_numeric($foco['x'] ?? null) ? max(0, min(100, (int) $foco['x'])) : $x;
            $y = is_numeric($foco['y'] ?? null) ? max(0, min(100, (int) $foco['y'])) : $y;
        }

        return $x.'% '.$y.'%';
    }
}
