<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnlaceCorto extends Model
{
    protected $table = 'enlaces_cortos';

    const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = ['activo' => 'boolean'];

    public function comuna(): BelongsTo
    {
        return $this->belongsTo(TerritorioComuna::class, 'comuna_id');
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function interacciones(): HasMany
    {
        return $this->hasMany(Interaccion::class, 'codigo_q', 'codigo');
    }

    public function url(): string
    {
        return url('/q/'.$this->codigo);
    }
}
