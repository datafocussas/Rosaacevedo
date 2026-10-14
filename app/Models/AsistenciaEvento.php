<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaEvento extends Model
{
    protected $table = 'asistencias_evento';

    public $incrementing = false;

    protected $primaryKey = null;

    protected $guarded = [];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class);
    }
}
