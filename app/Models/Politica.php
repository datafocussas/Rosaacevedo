<?php

namespace App\Models;

use App\Support\CacheSitio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LogicException;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Versión de un texto legal. Solo una vigente por tipo; las anteriores quedan de solo lectura
 * porque los consentimientos apuntan a ellas.
 */
class Politica extends Model
{
    use Concerns\LimpiaCacheSitio;
    use LogsActivity;

    public const TIPOS = [
        'tratamiento_datos' => 'Política de tratamiento de datos',
        'autorizacion_general' => 'Autorización general',
        'whatsapp' => 'Autorización de WhatsApp',
        'afinidad_politica' => 'Autorización de afinidad política',
        'publicar_propuesta' => 'Autorización para publicar la propuesta',
        'cookies' => 'Política de cookies',
        'uso_ia' => 'Uso de inteligencia artificial',
    ];

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['vigente_desde' => 'datetime', 'vigente' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Politica $politica) {
            $politica->hash_sha256 = hash('sha256', $politica->texto);
        });

        static::updating(function (Politica $politica) {
            if ($politica->isDirty(['texto', 'tipo', 'version']) && $politica->tieneConsentimientos()) {
                throw new LogicException('Esta versión ya tiene consentimientos: crea una versión nueva en lugar de editarla.');
            }
        });

        static::deleting(function (Politica $politica) {
            if ($politica->tieneConsentimientos()) {
                throw new LogicException('No se puede borrar una versión con consentimientos.');
            }
        });
    }

    public static function vigente(string $tipo): ?self
    {
        return static::query()->where('tipo', $tipo)->where('vigente', true)->first();
    }

    /** Marca esta versión como la vigente de su tipo. */
    public function activar(): void
    {
        DB::transaction(function () {
            static::query()->where('tipo', $this->tipo)->where('id', '!=', $this->id)->update(['vigente' => false]);
            $this->forceFill(['vigente' => true])->saveQuietly();
        });

        CacheSitio::limpiar();
    }

    public function tieneConsentimientos(): bool
    {
        return $this->exists && DB::table('consentimientos')->where('politica_id', $this->id)->exists();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['tipo', 'version', 'hash_sha256', 'vigente'])->logOnlyDirty()->useLogName('legal');
    }
}
