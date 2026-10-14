<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Eje extends Model implements HasMedia
{
    use Concerns\ConversionesWebp, InteractsWithMedia {
        Concerns\ConversionesWebp::registerMediaConversions insteadof InteractsWithMedia;
    }
    use Concerns\LimpiaCacheSitio;

    protected $guarded = ['id'];

    protected $casts = [
        'contenido' => 'array',
        'compromisos' => 'array',
        'publicado' => 'boolean',
    ];

    public function scopePublicados(Builder $query): Builder
    {
        return $query->where('publicado', true)->orderBy('orden');
    }

    public function noticias(): HasMany
    {
        return $this->hasMany(Noticia::class);
    }

    public function temas(): HasMany
    {
        return $this->hasMany(Tema::class);
    }

    /** «Por la salud», «Por una ciudad que avanza». */
    public function titulo(): string
    {
        return 'Por '.$this->articulo.' '.$this->sujeto;
    }

    /** Nombre corto para chips: «Salud», «Jóvenes». */
    public function nombreCorto(): string
    {
        return mb_strtoupper(mb_substr($this->sujeto, 0, 1)).mb_substr($this->sujeto, 1);
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
