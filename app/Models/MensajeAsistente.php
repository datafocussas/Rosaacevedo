<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajeAsistente extends Model
{
    protected $table = 'asistente_mensajes';

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(ConversacionAsistente::class, 'conversacion_id');
    }
}
