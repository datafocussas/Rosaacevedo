<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerritorioBarrio extends Model
{
    use Concerns\LimpiaCacheSitio;

    protected $table = 'territorio_barrio';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['activo' => 'boolean'];

    public function comuna(): BelongsTo
    {
        return $this->belongsTo(TerritorioComuna::class, 'comuna_2024_id');
    }

    public function comuna2007(): BelongsTo
    {
        return $this->belongsTo(TerritorioComuna::class, 'comuna_2007_id');
    }

    /** «Santa María 1 · Comuna 4» para los selectores. */
    public function etiquetaSelector(): string
    {
        return $this->nombre.' · '.($this->comuna?->esCorregimiento() ? 'El Manzanillo' : $this->comuna?->rotulo());
    }
}
