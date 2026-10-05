<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use Concerns\LimpiaCacheSitio;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['activo' => 'boolean'];
}
