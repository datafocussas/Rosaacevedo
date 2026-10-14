<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Noticia extends Model implements HasMedia
{
    use Concerns\ConversionesWebp, InteractsWithMedia {
        Concerns\ConversionesWebp::registerMediaConversions insteadof InteractsWithMedia;
    }
    use Concerns\LimpiaCacheSitio;
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = ['publicada_en' => 'datetime'];

    public function eje(): BelongsTo
    {
        return $this->belongsTo(Eje::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function comunas(): BelongsToMany
    {
        return $this->belongsToMany(TerritorioComuna::class, 'noticia_comuna', 'noticia_id', 'comuna_id');
    }

    /** Publicadas, o programadas cuya hora ya llegó. */
    public function scopePublicadas(Builder $query): Builder
    {
        return $query->whereIn('estado', ['publicada', 'programada'])
            ->whereNotNull('publicada_en')
            ->where('publicada_en', '<=', now())
            ->orderByDesc('publicada_en');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('destacada')->singleFile();
        $this->addMediaCollection('galeria');
        $this->addMediaCollection('cuerpo');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['titulo', 'slug', 'estado', 'publicada_en'])->logOnlyDirty()->useLogName('contenido');
    }
}
