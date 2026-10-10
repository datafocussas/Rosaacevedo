<?php

namespace App\Models;

use App\Support\Celular;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Un ciudadano = un registro. La llave es el HMAC del celular en E.164; el número
 * solo se guarda cifrado (cast encrypted con APP_KEY).
 */
class Ciudadano extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['celular_hash', 'celular_cifrado'];

    protected $casts = [
        'celular_cifrado' => 'encrypted',
        'primer_origen' => 'array',
        'es_voluntario' => 'boolean',
        'crm_sync_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ciudadano $ciudadano) {
            $ciudadano->uuid ??= (string) Str::uuid();
        });
    }

    public static function buscarPorCelular(string $celular): ?self
    {
        $e164 = Celular::normalizar($celular);

        return $e164 ? static::query()->where('celular_hash', Celular::hmac($e164))->first() : null;
    }

    public function asignarCelular(string $e164): void
    {
        $this->celular_hash = Celular::hmac($e164);
        $this->celular_cifrado = $e164;
    }

    public function celular(): string
    {
        return (string) $this->celular_cifrado;
    }

    public function celularEnmascarado(): string
    {
        return Celular::enmascarar($this->celular());
    }

    /** Código corto y estable para atribuir la conversación de WhatsApp (RF-06). */
    public function codigo(): string
    {
        return 'R-'.strtoupper(substr(str_replace('-', '', $this->uuid), 0, 6));
    }

    public function barrio(): BelongsTo
    {
        return $this->belongsTo(TerritorioBarrio::class, 'barrio_id');
    }

    /** Comuna 2024 elegida en el formulario (o derivada del barrio). */
    public function comuna(): BelongsTo
    {
        return $this->belongsTo(TerritorioComuna::class, 'comuna_id');
    }

    public function consentimientos(): HasMany
    {
        return $this->hasMany(Consentimiento::class);
    }

    public function interacciones(): HasMany
    {
        return $this->hasMany(Interaccion::class);
    }

    public function propuestas(): HasMany
    {
        return $this->hasMany(PropuestaCiudadana::class);
    }

    public function voluntariado(): HasOne
    {
        return $this->hasOne(Voluntariado::class);
    }

    /** Estado vigente de cada tipo de autorización: el último registro por tipo. */
    public function consentimientosVigentes(): array
    {
        $vigentes = [];

        foreach ($this->consentimientos()->with('politica')->orderBy('created_at')->orderBy('id')->get() as $consentimiento) {
            $vigentes[$consentimiento->politica->tipo] = $consentimiento->otorgado;
        }

        return $vigentes;
    }
}
