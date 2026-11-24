<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Conversación anónima con el asistente. Sin datos de contacto; se borra a los días de retención del panel. */
class ConversacionAsistente extends Model
{
    protected $table = 'asistente_conversaciones';

    protected $guarded = ['id'];

    public function mensajesLista(): HasMany
    {
        return $this->hasMany(MensajeAsistente::class, 'conversacion_id')->orderBy('id');
    }
}
