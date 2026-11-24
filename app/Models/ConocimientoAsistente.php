<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** Ficha de la base de conocimiento del asistente: un tema con su texto y la fuente que lo respalda. */
class ConocimientoAsistente extends Model
{
    use LogsActivity;

    protected $table = 'asistente_conocimiento';

    protected $guarded = ['id'];

    protected $casts = ['activo' => 'boolean'];

    public const CATEGORIAS = [
        'trayectoria' => 'Trayectoria y biografía',
        'propuestas' => 'Propuestas',
        'vision' => 'Visión de Itagüí',
        'comunas' => 'Comunas y barrios',
        'campana' => 'La campaña: cómo participar, eventos, contacto',
        'preguntas' => 'Preguntas frecuentes',
        'otro' => 'Otro',
    ];

    protected static function booted(): void
    {
        static::saving(fn (self $ficha) => $ficha->updated_by = auth()->id() ?? $ficha->updated_by);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true)->orderBy('categoria')->orderBy('orden')->orderBy('id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['titulo', 'categoria', 'contenido', 'fuente', 'activo'])->logOnlyDirty()->useLogName('asistente');
    }
}
