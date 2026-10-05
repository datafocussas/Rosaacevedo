<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Evidencia de autorización (Ley 1581 de 2012, Circular SIC 002 de 2026).
 * Solo inserción: cada otorgamiento o revocatoria es una fila nueva.
 */
class Consentimiento extends Model
{
    const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'otorgado' => 'boolean',
        'created_at' => 'datetime:Y-m-d\TH:i:s.vP',
    ];

    protected $dateFormat = 'Y-m-d H:i:s.v';

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('La tabla consentimientos es de solo inserción.'));
        static::deleting(fn () => throw new LogicException('La tabla consentimientos es de solo inserción.'));
    }

    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class);
    }

    public function politica(): BelongsTo
    {
        return $this->belongsTo(Politica::class);
    }

    public function ipLegible(): string
    {
        return @inet_ntop($this->ip) ?: '';
    }
}
