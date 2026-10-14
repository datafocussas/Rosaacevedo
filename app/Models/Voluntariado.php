<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voluntariado extends Model
{
    protected $table = 'voluntariado';

    protected $primaryKey = 'ciudadano_id';

    public $incrementing = false;

    const CREATED_AT = null;

    protected $guarded = [];

    /** Opciones de «¿Cómo quieres ayudar?» (paso 3 del registro). */
    public const INTERESES = [
        'vecinos' => 'Hablar con mis vecinos',
        'redes' => 'Compartir contenido en redes',
        'eventos' => 'Apoyar eventos en mi comuna',
        'testigo' => 'Ser testigo electoral',
        'transporte' => 'Apoyar con transporte',
    ];

    public const DISPONIBILIDAD = ['semana' => 'Entre semana', 'fin_de_semana' => 'Fines de semana', 'noches' => 'En las noches'];

    protected $casts = ['intereses' => 'array', 'disponibilidad' => 'array'];

    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class);
    }
}
