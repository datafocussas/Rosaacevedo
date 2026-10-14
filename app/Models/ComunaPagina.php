<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ComunaPagina extends Model implements HasMedia
{
    use Concerns\ConversionesWebp, InteractsWithMedia {
        Concerns\ConversionesWebp::registerMediaConversions insteadof InteractsWithMedia;
    }
    use Concerns\LimpiaCacheSitio;

    const CREATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = ['bloques' => 'array', 'publicada' => 'boolean'];

    public function comuna(): BelongsTo
    {
        return $this->belongsTo(TerritorioComuna::class, 'comuna_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('imagen')->singleFile();
    }
}
