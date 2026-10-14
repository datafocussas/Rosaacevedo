<?php

namespace App\Console\Commands;

use App\Http\Middleware\AsignarVariante;
use App\Models\Banner;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

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
                foreach (['w400', 'w800', 'w1200', 'w1600'] as $conversion) {
                    if ($foto->hasGeneratedConversion($conversion) && ! is_file($foto->getPath($conversion))) {
                        $problemas[] = "La versión optimizada {$conversion} de «{$nombre}» está marcada como lista pero el archivo no existe.";
                    }
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

        $this->newLine();
        $this->line('<options=bold>Revisión general</>');
        $enlace = public_path('storage');
        if (! is_link($enlace) && ! is_dir($enlace)) {
            $this->error('  Falta el enlace public/storage: ejecuta «php artisan storage:link». Sin él ninguna foto subida se ve en el sitio.');
        } elseif (is_link($enlace) && ! is_dir($enlace)) {
            $this->error('  El enlace public/storage apunta a una carpeta que no existe ('.readlink($enlace).'). Bórralo y ejecuta «php artisan storage:link».');
        } else {
            $this->info('  Enlace public/storage correcto.');
        }
        $this->line('  APP_URL: '.config('app.url'));
        $this->line('  Caché de páginas completas: '.(config('responsecache.enabled') ? 'ACTIVA (si ves una versión vieja: php artisan responsecache:clear)' : 'apagada'));

        // Lo que de verdad recibe el navegador en la página de inicio.
        $respuesta = app()->handle(Request::create('/', 'GET'));
        $html = (string) $respuesta->getContent();
        preg_match('/<section[^>]*ra-entrada.*?<\/section>/s', $html, $entrada);
        preg_match_all('/(?:src|srcset)="([^"]+)"/', $entrada[0] ?? '', $atributos);
        $urls = collect($atributos[1] ?? [])->flatMap(fn ($v) => array_map(fn ($p) => trim(explode(' ', trim($p))[0]), explode(',', $v)))
            ->filter(fn ($u) => str_contains($u, '/storage/'))->unique()->values();
        $this->line('  Inicio responde: HTTP '.$respuesta->getStatusCode());
        if ($urls->isEmpty()) {
            $this->warn('  La entrada de inicio NO incluye ninguna foto de banner: el sitio está mostrando el banner por defecto (ningún banner vigente con foto).');
        }
        foreach ($urls as $url) {
            $ruta = public_path(ltrim(parse_url($url, PHP_URL_PATH), '/'));
            $this->line('  '.(is_file($ruta) ? '<fg=green>OK</>      ' : '<fg=red>FALTA</>   ').$url);
            $this->line('           Ábrela en el navegador: '.rtrim(config('app.url'), '/').$url);
        }

        return self::SUCCESS;
    }
}
