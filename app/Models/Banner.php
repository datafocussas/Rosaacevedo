<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
    ];

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
        $this->addMediaCollection('escritorio')->singleFile();
        $this->addMediaCollection('movil')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['titular', 'estado', 'variante', 'publicar_desde', 'publicar_hasta'])->logOnlyDirty()->useLogName('contenido');
    }
}
