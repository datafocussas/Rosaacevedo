<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Redireccion extends Model
{
    use Concerns\LimpiaCacheSitio;
    use LogsActivity;

    protected $table = 'redirecciones';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** Guarda la ruta sin dominio y sin barra final: «/quienes-somos». */
    public static function normalizar(string $ruta): string
    {
        $ruta = parse_url($ruta, PHP_URL_PATH) ?: '/';

        return '/'.trim($ruta, '/');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['desde', 'hacia', 'codigo'])->logOnlyDirty()->useLogName('configuracion');
    }
}
