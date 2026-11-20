<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/** Columna de opinión. Mismo flujo editorial que las noticias: borrador, revisión, programada, publicada, archivada. */
class Columna extends Model implements HasMedia
{
    use Concerns\ConversionesWebp, InteractsWithMedia {
        Concerns\ConversionesWebp::registerMediaConversions insteadof InteractsWithMedia;
    }
    use Concerns\LimpiaCacheSitio;
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = ['publicada_en' => 'datetime'];

    public function columnista(): BelongsTo
    {
        return $this->belongsTo(Columnista::class);
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
    }

    /** Minutos de lectura a 200 palabras por minuto. */
    public function minutosLectura(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->cuerpo)) / 200));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['titulo', 'slug', 'estado', 'publicada_en', 'columnista_id'])->logOnlyDirty()->useLogName('contenido');
    }
}
