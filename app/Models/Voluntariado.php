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

    protected $casts = ['intereses' => 'array', 'disponibilidad' => 'array'];

    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class);
    }
}
