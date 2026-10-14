<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Evento extends Model implements HasMedia
{
    use Concerns\ConversionesWebp, InteractsWithMedia {
        Concerns\ConversionesWebp::registerMediaConversions insteadof InteractsWithMedia;
    }
    use Concerns\LimpiaCacheSitio;

    protected $guarded = ['id'];

    protected $casts = [
        'inicia_en' => 'datetime',
        'termina_en' => 'datetime',
        'publico' => 'boolean',
    ];

    public function comuna(): BelongsTo
    {
        return $this->belongsTo(TerritorioComuna::class, 'comuna_id');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaEvento::class);
    }

    public function scopeProximos(Builder $query): Builder
    {
        return $query->where('estado', 'publicado')->where('publico', true)
            ->where('inicia_en', '>=', now()->startOfDay())->orderBy('inicia_en');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('imagen')->singleFile();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
