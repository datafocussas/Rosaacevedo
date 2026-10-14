<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tema extends Model
{
    use Concerns\LimpiaCacheSitio;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['activo' => 'boolean'];

    public function eje(): BelongsTo
    {
        return $this->belongsTo(Eje::class);
    }
}
