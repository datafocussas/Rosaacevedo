<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TerritorioComuna extends Model
{
    protected $table = 'territorio_comuna';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['area_ha' => 'decimal:2'];

    /** División vigente para el sitio público: Acuerdo 017 de 2024. */
    public function scopeVigente(Builder $query): Builder
    {
        return $query->where('division', '2024')->orderByRaw("CASE WHEN codigo = 'CORR' THEN 1 ELSE 0 END")->orderBy('codigo');
    }

    public function barrios(): HasMany
    {
        return $this->hasMany(TerritorioBarrio::class, 'comuna_2024_id');
    }

    public function barrios2007(): HasMany
    {
        return $this->hasMany(TerritorioBarrio::class, 'comuna_2007_id');
    }

    public function pagina(): HasOne
    {
        return $this->hasOne(ComunaPagina::class, 'comuna_id');
    }

    public function esCorregimiento(): bool
    {
        return $this->tipo === 'corregimiento';
    }

    /** «Comuna 4» o «Corregimiento». */
    public function rotulo(): string
    {
        return $this->esCorregimiento() ? 'Corregimiento' : 'Comuna '.ltrim(substr($this->codigo, 1), '0');
    }

    /** Nombre en el sitio público: solo el número de la comuna; el corregimiento con su nombre. */
    public function nombrePublico(): string
    {
        return $this->esCorregimiento() ? 'Corregimiento El Manzanillo' : $this->rotulo();
    }

    /** Opciones para los selectores del sitio: [id => «Comuna 1»…, «Corregimiento El Manzanillo»]. */
    public static function opcionesPublicas(): array
    {
        return static::query()->vigente()->get()->mapWithKeys(fn (self $c) => [$c->id => $c->nombrePublico()])->all();
    }

    /** «Santa María» a partir de «Comuna 4 · Santa María». */
    public function nombreCorto(): string
    {
        if ($this->esCorregimiento()) {
            return trim(str_replace('Corregimiento', '', $this->nombre));
        }

        $partes = explode(' · ', $this->nombre, 2);

        return $partes[1] ?? $this->nombre;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
