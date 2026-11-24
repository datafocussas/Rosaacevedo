<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Enlaces de videos y redes sociales que se ven dentro de la página en lugar de un enlace plano:
 * YouTube y Vimeo (portada que reproduce al hacer clic) e Instagram, TikTok, X y Facebook (la publicación en un
 * marco de la red). Solo con iframes oficiales de cada red, sin scripts de terceros; las redes se cargan cuando
 * la persona acepta las cookies de redes sociales o hace clic en «Mostrar la publicación aquí».
 */
final class Incrustacion
{
    public const NOMBRES = [
        'youtube' => 'YouTube', 'vimeo' => 'Vimeo', 'instagram' => 'Instagram',
        'tiktok' => 'TikTok', 'x' => 'X', 'facebook' => 'Facebook',
    ];

    /** Dominios que la CSP deja mostrar en marcos (frame-src). */
    public const MARCOS = [
        'https://www.youtube-nocookie.com', 'https://player.vimeo.com', 'https://www.instagram.com',
        'https://www.tiktok.com', 'https://platform.twitter.com', 'https://www.facebook.com',
    ];

    /**
     * Datos para mostrar una publicación o video, o null si el enlace no es de una red conocida.
     *
     * @return array{tipo: 'video'|'red', red: string, nombre: string, url: string, src: string, alto: int, ancho: int, vertical: bool, miniatura: ?string}|null
     */
    public static function de(?string $url): ?array
    {
        $url = trim(html_entity_decode((string) $url, ENT_QUOTES | ENT_HTML5));
        if (! preg_match('~^https?://~i', $url)) {
            return null;
        }
        $base = ['url' => $url, 'alto' => 0, 'ancho' => 0, 'vertical' => false, 'miniatura' => null];

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $url, $m)) {
            return ['tipo' => 'video', 'red' => 'youtube', 'nombre' => 'YouTube',
                'src' => 'https://www.youtube-nocookie.com/embed/'.$m[1].'?rel=0',
                'vertical' => str_contains(strtolower($url), '/shorts/'),
                'miniatura' => 'https://i.ytimg.com/vi/'.$m[1].'/hqdefault.jpg', 'alto' => 0, 'ancho' => 0, 'url' => $url];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $m)) {
            return ['tipo' => 'video', 'red' => 'vimeo', 'nombre' => 'Vimeo', 'src' => 'https://player.vimeo.com/video/'.$m[1].'?dnt=1'] + $base;
        }

        if (preg_match('~instagram\.com/(?:[A-Za-z0-9_.]+/)?(p|reel|reels|tv)/([A-Za-z0-9_-]+)~i', $url, $m)) {
            $tipo = strtolower($m[1]) === 'p' ? 'p' : 'reel';

            return ['tipo' => 'red', 'red' => 'instagram', 'nombre' => 'Instagram',
                'src' => 'https://www.instagram.com/'.$tipo.'/'.$m[2].'/embed/captioned/', 'alto' => 740, 'ancho' => 500] + $base;
        }

        if (preg_match('~tiktok\.com/@[^/]+/(?:video|photo)/(\d+)~i', $url, $m)) {
            return ['tipo' => 'red', 'red' => 'tiktok', 'nombre' => 'TikTok',
                'src' => 'https://www.tiktok.com/embed/v2/'.$m[1].'?lang=es', 'alto' => 740, 'ancho' => 340] + $base;
        }

        if (preg_match('~(?:twitter|x)\.com/[A-Za-z0-9_]+/status(?:es)?/(\d+)~i', $url, $m)) {
            return ['tipo' => 'red', 'red' => 'x', 'nombre' => 'X',
                'src' => 'https://platform.twitter.com/embed/Tweet.html?id='.$m[1].'&dnt=true&lang=es', 'alto' => 620, 'ancho' => 550] + $base;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (preg_match('~(^|\.)(facebook\.com|fb\.watch)$~', $host)) {
            $ruta = strtolower((string) parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY));
            $esVideo = $host === 'fb.watch' || preg_match('~/(videos|reel|watch|share/v|share/r)\b~', $ruta);
            $esPublicacion = $esVideo || preg_match('~/(posts|permalink\.php|photo|story\.php|share/p)\b~', $ruta);
            if (! $esPublicacion) {
                return null; // Una página o perfil: eso va en el bloque de redes (perfil()).
            }

            return ['tipo' => 'red', 'red' => 'facebook', 'nombre' => 'Facebook',
                'src' => 'https://www.facebook.com/plugins/'.($esVideo ? 'video' : 'post').'.php?href='.rawurlencode($url).'&show_text=true&width=500',
                'alto' => $esVideo ? 640 : 680, 'ancho' => 500] + $base;
        }

        return null;
    }

    /**
     * Widget del perfil de una red (Facebook: últimas publicaciones de la página; Instagram y TikTok: perfil con
     * sus publicaciones recientes). Null para redes sin widget de perfil (X, YouTube, WhatsApp).
     *
     * @return array{tipo: 'red', red: string, nombre: string, titulo: string, url: string, src: string, alto: int, ancho: int, vertical: bool, miniatura: null}|null
     */
    public static function perfil(?string $url): ?array
    {
        $url = trim((string) $url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $ruta = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $base = ['tipo' => 'red', 'url' => $url, 'vertical' => false, 'miniatura' => null];

        if (preg_match('~(^|\.)facebook\.com$~', $host) && $ruta !== '') {
            return ['red' => 'facebook', 'nombre' => 'Facebook', 'titulo' => 'Página de Facebook', 'alto' => 700, 'ancho' => 500,
                'src' => 'https://www.facebook.com/plugins/page.php?href='.rawurlencode($url).'&tabs=timeline&width=500&height=700&small_header=true&adapt_container_width=true&hide_cover=false&locale=es_LA'] + $base;
        }

        if (preg_match('~(^|\.)instagram\.com$~', $host) && preg_match('~^([A-Za-z0-9_.]+)$~', $ruta, $m)) {
            return ['red' => 'instagram', 'nombre' => 'Instagram', 'titulo' => 'Perfil de Instagram', 'alto' => 720, 'ancho' => 500,
                'src' => 'https://www.instagram.com/'.$m[1].'/embed/'] + $base;
        }

        if (preg_match('~(^|\.)tiktok\.com$~', $host) && preg_match('~^@([A-Za-z0-9_.]+)$~', $ruta, $m)) {
            return ['red' => 'tiktok', 'nombre' => 'TikTok', 'titulo' => 'Perfil de TikTok', 'alto' => 720, 'ancho' => 500,
                'src' => 'https://www.tiktok.com/embed/@'.$m[1].'?lang=es'] + $base;
        }

        return null;
    }

    /**
     * En el HTML del editor, cada enlace a un video o publicación cuyo texto es la misma dirección (es decir, un
     * enlace pegado, no unas palabras enlazadas) se muestra incrustado. Si el enlace está solo en su párrafo,
     * reemplaza el párrafo; si está en medio de un texto, la publicación aparece justo después de ese párrafo.
     */
    public static function enHtml(string $html): string
    {
        if (! preg_match('~(youtu|vimeo|instagram|tiktok|twitter|x\.com|facebook|fb\.watch)~i', $html)) {
            return $html;
        }

        $documento = new DOMDocument('1.0', 'UTF-8');
        $anterior = libxml_use_internal_errors(true);
        $documento->loadHTML('<?xml encoding="UTF-8"><div id="ra-raiz">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($anterior);

        $raiz = $documento->getElementById('ra-raiz');
        $cambios = 0;
        $guardadas = [];

        foreach (iterator_to_array((new DOMXPath($documento))->query('//a[@href]')) as $enlace) {
            /** @var DOMElement $enlace */
            $datos = self::de($enlace->getAttribute('href'));
            $texto = trim($enlace->textContent);
            if (! $datos || ! self::mismaDireccion($texto, $enlace->getAttribute('href'))) {
                continue;
            }

            // El bloque que contiene el enlace (párrafo, div o elemento de lista), hijo directo de la raíz.
            $bloque = $enlace;
            while ($bloque->parentNode && $bloque->parentNode !== $raiz) {
                $bloque = $bloque->parentNode;
            }
            if (! $bloque->parentNode) {
                continue;
            }

            $marcador = $documento->createElement('div');
            $marcador->setAttribute('data-ra-incrustacion', (string) $cambios);
            $guardadas[$cambios] = $datos;

            if (trim($bloque->textContent) === $texto) {
                $bloque->parentNode->replaceChild($marcador, $bloque);
            } else {
                $bloque->parentNode->insertBefore($marcador, $bloque->nextSibling);
            }
            $cambios++;
        }

        if ($cambios === 0) {
            return $html;
        }

        $salida = '';
        foreach ($raiz->childNodes as $nodo) {
            $salida .= $documento->saveHTML($nodo);
        }

        return preg_replace_callback('~<div data-ra-incrustacion="(\d+)"></div>~', fn ($m) => view('partials.incrustacion', ['datos' => $guardadas[(int) $m[1]]])->render(), $salida);
    }

    private static function mismaDireccion(string $texto, string $href): bool
    {
        $limpiar = fn (string $u) => rtrim(preg_replace('~^https?://(www\.)?~i', '', trim($u)), '/');

        return $texto !== '' && strcasecmp($limpiar($texto), $limpiar($href)) === 0;
    }
}
