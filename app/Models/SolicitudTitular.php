<?php

namespace App\Models;

use App\Support\DiasHabiles;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** Derechos del titular (Ley 1581 de 2012, arts. 14 y 15). */
class SolicitudTitular extends Model
{
    use LogsActivity;

    public const TIPOS = [
        'consulta' => 'Consultar mis datos',
        'actualizacion' => 'Actualizar o corregir mis datos',
        'supresion' => 'Suprimir mis datos',
        'revocatoria' => 'Retirar mi autorización',
        'reclamo' => 'Presentar un reclamo',
    ];

    protected $table = 'solicitudes_titular';

    protected $guarded = ['id'];

    protected $casts = ['vence_en' => 'date'];

    /** 10 días hábiles para consultas; 15 para reclamos, supresiones, actualizaciones y revocatorias. */
    public static function calcularVencimiento(string $tipo, ?\DateTimeInterface $desde = null): Carbon
    {
        return DiasHabiles::sumar($desde ?? now(), $tipo === 'consulta' ? 10 : 15);
    }

    public static function siguienteRadicado(): string
    {
        $prefijo = 'ST-'.now()->year.'-';
        $ultimo = static::query()->where('radicado', 'like', $prefijo.'%')->lockForUpdate()->max('radicado');
        $numero = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        return $prefijo.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class);
    }

    public function atendidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendida_por');
    }

    /** verde, amarillo (≤ 3 días hábiles) o rojo (vencida). */
    public function semaforo(): string
    {
        if (in_array($this->estado, ['respondida', 'cerrada'], true)) {
            return 'cerrada';
        }

        if ($this->vence_en->isPast() && ! $this->vence_en->isToday()) {
            return 'vencida';
        }

        return DiasHabiles::entre(now(), $this->vence_en) <= 3 ? 'por_vencer' : 'en_plazo';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['estado', 'respuesta', 'atendida_por'])->logOnlyDirty()->useLogName('titular');
    }
}
