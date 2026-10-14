<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedSocial extends Model
{
    use Concerns\LimpiaCacheSitio;

    protected $table = 'redes_sociales';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['activa' => 'boolean'];
}
