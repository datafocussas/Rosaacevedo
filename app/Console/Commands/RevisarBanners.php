<?php

namespace App\Console\Commands;

use App\Http\Middleware\AsignarVariante;
use App\Models\Banner;
use Illuminate\Console\Command;

/**
 * Diagnóstico de banners: dice, banner por banner, si sale en el sitio y por qué no, y si sus fotos existen.
 * Uso: php artisan banners:revisar
 */
class RevisarBanners extends Command
{
    protected $signature = 'banners:revisar';

    protected $description = 'Revisa si cada banner se muestra en el sitio y si sus fotos están en el servidor.';

    public function handle(): int
    {
        $banners = Banner::query()->with('media', 'comuna')->orderBy('pagina')->orderBy('orden')->get();

        if ($banners->isEmpty()) {
            $this->warn('No hay banners. En inicio se ve el banner por defecto, sin foto.');

            return self::SUCCESS;
        }

        $vigentes = Banner::query()->vigentes()->pluck('id')->all();
        $variantesActivas = AsignarVariante::variantesActivas();

        foreach ($banners as $banner) {
            $this->newLine();
            $this->line("<options=bold>#{$banner->id} · {$banner->titular}</>");
            $this->line('  Página: '.($banner->pagina === 'comuna' ? 'comuna '.($banner->comuna?->nombre ?? '(sin comuna)') : 'inicio'));

            $problemas = [];
            if ($banner->estado !== 'activo') {
                $problemas[] = "Su estado es «{$banner->estado}»: solo se muestran los «Activo».";
            }
            if ($banner->publicar_desde && $banner->publicar_desde->isFuture()) {
                $problemas[] = 'Empieza a mostrarse el '.$banner->publicar_desde->format('d/m/Y g:i a').' (hora de Colombia).';
            }
            if ($banner->publicar_hasta && $banner->publicar_hasta->isPast()) {
                $problemas[] = 'Dejó de mostrarse el '.$banner->publicar_hasta->format('d/m/Y g:i a').'.';
            }
            if ($banner->variante && $variantesActivas !== []) {
                $problemas[] = "Tiene la variante A/B «{$banner->variante}»: solo lo ve parte de los visitantes. Déjala vacía si no estás probando.";
            }

            foreach (['escritorio' => 'Escritorio (16:9)', 'movil' => 'Móvil (4:5)'] as $coleccion => $nombre) {
                $foto = $banner->getFirstMedia($coleccion);
                if (! $foto) {
                    $this->line("  {$nombre}: sin foto");

                    continue;
                }
                $existe = is_file($foto->getPath());
                $this->line("  {$nombre}: {$foto->file_name} · ".($existe ? 'archivo presente' : '<fg=red>EL ARCHIVO NO ESTÁ EN EL SERVIDOR</>').' · '.$foto->getUrl());
                if (! $existe) {
                    $problemas[] = "La foto «{$nombre}» está registrada pero el archivo no existe: vuelve a subirla.";
                }
            }
            if (! $banner->getFirstMedia('escritorio') && ! $banner->getFirstMedia('movil')) {
                $problemas[] = 'No tiene ninguna foto.';
            }

            if (in_array($banner->id, $vigentes, true) && $problemas === []) {
                $this->info('  Se muestra en el sitio.');
            } else {
                foreach ($problemas as $problema) {
                    $this->warn('  - '.$problema);
                }
            }
        }

        if (! is_link(public_path('storage')) && ! is_dir(public_path('storage'))) {
            $this->newLine();
            $this->error('Falta el enlace public/storage: ejecuta «php artisan storage:link». Sin él ninguna foto subida se ve en el sitio.');
        }

        return self::SUCCESS;
    }
}
