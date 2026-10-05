<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Pagina extends Model implements HasMedia
{
    use Concerns\ConversionesWebp, InteractsWithMedia {
        Concerns\ConversionesWebp::registerMediaConversions insteadof InteractsWithMedia;
    }
    use Concerns\LimpiaCacheSitio;
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = ['bloques' => 'array'];

    public function scopePublicadas(Builder $query): Builder
    {
        return $query->where('estado', 'publicada');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('bloques');
        $this->addMediaCollection('og')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['titulo', 'slug', 'estado'])->logOnlyDirty()->useLogName('contenido');
    }
}
