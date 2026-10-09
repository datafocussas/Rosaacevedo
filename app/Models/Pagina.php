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

    /** Páginas con ruta propia en el código: su dirección no se cambia desde el panel. */
    public const FIJAS = ['inicio', 'conoce-a-rosa', 'manifiesto', 'uso-de-ia'];

    /** Direcciones que ya usa el sitio y no pueden ser la ruta de una página. */
    public const RESERVADAS = [
        'admin', 'api', 'livewire', 'filament', 'storage', 'build', 'css', 'js', 'img', 'fonts', 'up',
        'propuestas', 'comunas', 'noticias', 'agenda', 'buzon', 'sumate', 'enlaces', 'mis-datos',
        'politica-de-datos', 'transparencia', 'q', 'vista-previa', 'sitemap', 'robots', 'prensa', 'participa',
    ];

    protected static function booted(): void
    {
        // Al borrar la página, sus enlaces del menú también se van.
        static::deleted(fn (Pagina $pagina) => MenuItem::query()->where('url', $pagina->ruta())->delete());
    }

    /** «/» para inicio; «/mi-pagina» para las demás. */
    public function ruta(): string
    {
        return $this->slug === 'inicio' ? '/' : '/'.$this->slug;
    }

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
