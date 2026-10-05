<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Configuración global (clave → valor JSON). Los cambios se registran en la bitácora desde App\Services\Ajustes::set(). */
class Ajuste extends Model
{
    use Concerns\LimpiaCacheSitio;

    protected $table = 'ajustes';

    protected $primaryKey = 'clave';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = null;

    protected $guarded = [];

    protected $casts = ['valor' => 'json'];
}
