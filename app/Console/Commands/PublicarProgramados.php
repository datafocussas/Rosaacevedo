<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Noticia;
use App\Support\CacheSitio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Publica noticias programadas y limpia la caché cuando un banner entra o sale de su ventana
 * de publicación (criterio de aceptación 5). Corre cada minuto.
 */
class PublicarProgramados extends Command
{
    protected $signature = 'sitio:publicar-programados';

    protected $description = 'Publica noticias y banners programados.';

    public function handle(): int
    {
        $ahora = now();
        $ultima = Cache::get('publicar-programados:ultima', $ahora->copy()->subMinutes(2));

        $noticias = Noticia::query()->where('estado', 'programada')->whereNotNull('publicada_en')->where('publicada_en', '<=', $ahora)->get();
        foreach ($noticias as $noticia) {
            $noticia->update(['estado' => 'publicada']);
        }

        $cambioBanners = Banner::query()->where('estado', 'activo')->where(function ($q) use ($ultima, $ahora) {
            $q->whereBetween('publicar_desde', [$ultima, $ahora])->orWhereBetween('publicar_hasta', [$ultima, $ahora]);
        })->exists();

        if ($noticias->isNotEmpty() || $cambioBanners) {
            CacheSitio::limpiar();
            $this->info("Publicadas {$noticias->count()} noticias; caché limpiada.");
        }

        Cache::put('publicar-programados:ultima', $ahora, 86400);

        return self::SUCCESS;
    }
}
