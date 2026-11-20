<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Banner extends Model implements HasMedia
{
    use Concerns\ConversionesWebp, InteractsWithMedia {
        Concerns\ConversionesWebp::registerMediaConversions insteadof InteractsWithMedia;
    }
    use Concerns\LimpiaCacheSitio;
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = [
        'mostrar_lema' => 'boolean',
        'publicar_desde' => 'datetime',
        'publicar_hasta' => 'datetime',
        'segundos_rotacion' => 'integer',
    ];

    public const MAX_FOTOS = 8;

    public function comuna(): BelongsTo
    {
        return $this->belongsTo(TerritorioComuna::class, 'comuna_id');
    }

    /** Activos y dentro de su ventana de publicación (RF-10). */
    public function scopeVigentes(Builder $query): Builder
    {
        $ahora = now();

        return $query->where('estado', 'activo')
            ->where(fn ($q) => $q->whereNull('publicar_desde')->orWhere('publicar_desde', '<=', $ahora))
            ->where(fn ($q) => $q->whereNull('publicar_hasta')->orWhere('publicar_hasta', '>', $ahora))
            ->orderBy('orden');
    }

    public function registerMediaCollections(): void
    {
        // Varias fotos por banner: rotan en el orden en que quedan en el panel.
        $this->addMediaCollection('escritorio');
        $this->addMediaCollection('movil');
    }

    /**
     * Fotos del banner en orden, cada una con su versión de escritorio y de móvil. La foto de móvil N acompaña a la
     * de escritorio N; si falta una de las dos se usa la otra (basta subir fotos en una sola colección).
     *
     * @return list<array{escritorio: Media, movil: Media}>
     */
    public function fotos(): array
    {
        $escritorio = $this->getMedia('escritorio')->values();
        $movil = $this->getMedia('movil')->values();
        $fotos = [];

        for ($i = 0, $total = max($escritorio->count(), $movil->count()); $i < $total; $i++) {
            $e = $escritorio->get($i) ?? $movil->get($i);
            $fotos[] = ['escritorio' => $e, 'movil' => $movil->get($i) ?? $e];
        }

        return $fotos;
    }

    /** Segundos de cada foto, siempre entre 3 y 30. */
    public function segundos(): int
    {
        return max(3, min(30, (int) ($this->segundos_rotacion ?: 6)));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['titular', 'estado', 'variante', 'publicar_desde', 'publicar_hasta', 'segundos_rotacion'])->logOnlyDirty()->useLogName('contenido');
    }
}
