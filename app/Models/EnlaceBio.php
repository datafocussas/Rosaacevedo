<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnlaceBio extends Model
{
    use Concerns\LimpiaCacheSitio;

    protected $table = 'enlaces_bio';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['activo' => 'boolean'];
}
