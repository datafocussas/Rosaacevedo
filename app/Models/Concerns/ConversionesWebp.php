<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Conversiones automáticas a WebP en 400, 800, 1200 y 1600 px (sección 03, Galería y medios)
 * y la imagen para redes de 1200 × 630 (RF-32). Corren en cola.
 */
trait ConversionesWebp
{
    public function registerMediaConversions(?Media $media = null): void
    {
        foreach ([400, 800, 1200, 1600] as $ancho) {
            $this->addMediaConversion('w'.$ancho)
                ->format('webp')
                ->quality(78)
                ->width($ancho)
                ->queued();
        }

        $this->addMediaConversion('og')
            ->format('jpg')
            ->quality(80)
            ->fit(Fit::Crop, 1200, 630)
            ->queued();
    }
}
