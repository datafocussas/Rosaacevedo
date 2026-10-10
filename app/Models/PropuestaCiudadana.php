<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PropuestaCiudadana extends Model implements HasMedia
{
    use InteractsWithMedia;
    use LogsActivity;

    public const ESTADOS = [
        'recibida' => 'Recibida',
        'en_revision' => 'En revisión',
        'incorporada' => 'Incorporada',
        'respondida' => 'Respondida',
        'descartada' => 'Descartada',
    ];

    protected $table = 'propuestas_ciudadanas';

    protected $guarded = ['id'];

    protected $casts = [
        'sugerencia_ia' => 'array',
        'publicar_anonima' => 'boolean',
        'destacada' => 'boolean',
    ];

    /** PR-AAAA-NNNN, consecutivo por año. Se llama dentro de una transacción. */
    public static function siguienteCodigo(): string
    {
        $prefijo = 'PR-'.now()->year.'-';
        $ultimo = static::query()->where('codigo', 'like', $prefijo.'%')->lockForUpdate()->max('codigo');
        $numero = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        return $prefijo.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class);
    }

    public function tema(): BelongsTo
    {
        return $this->belongsTo(Tema::class);
    }

    public function temaConfirmado(): BelongsTo
    {
        return $this->belongsTo(Tema::class, 'tema_confirmado_id');
    }

    public function barrio(): BelongsTo
    {
        return $this->belongsTo(TerritorioBarrio::class, 'barrio_id');
    }

    /** Comuna 2024 elegida en el formulario (o derivada del barrio). */
    public function comuna(): BelongsTo
    {
        return $this->belongsTo(TerritorioComuna::class, 'comuna_id');
    }

    public function asignada(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignada_a');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('foto')->singleFile()->useDisk('local');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['estado', 'tema_confirmado_id', 'asignada_a', 'destacada', 'respuesta'])->logOnlyDirty()->useLogName('propuestas');
    }
}
