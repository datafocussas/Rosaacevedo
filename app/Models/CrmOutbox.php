<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmOutbox extends Model
{
    protected $table = 'crm_outbox';

    protected $guarded = ['id'];

    protected $casts = ['payload' => 'array', 'proximo_intento' => 'datetime'];
}
