<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interaccion extends Model
{
    protected $table = 'interacciones';

    const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class);
    }

    public function comunaPagina(): BelongsTo
    {
        return $this->belongsTo(TerritorioComuna::class, 'comuna_pagina_id');
    }
}
