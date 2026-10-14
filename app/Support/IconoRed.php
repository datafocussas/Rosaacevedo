<?php

namespace App\Support;

/**
 * Ícono de una red a partir de su dirección. Si en el panel quedó «Otro» (enlace) o vacío, pero la dirección es
 * de una red conocida, se usa el ícono de esa red; así un botón de YouTube muestra el logo de YouTube aunque se
 * haya elegido mal el ícono.
 */
final class IconoRed
{
    private const DOMINIOS = [
        'youtube.com' => 'youtube', 'youtu.be' => 'youtube',
        'facebook.com' => 'facebook', 'fb.com' => 'facebook', 'fb.me' => 'facebook',
        'instagram.com' => 'instagram',
        'tiktok.com' => 'tiktok',
        'x.com' => 'x', 'twitter.com' => 'x',
        'whatsapp.com' => 'whatsapp', 'wa.me' => 'whatsapp',
    ];

    public static function para(?string $icono, ?string $url): ?string
    {
        if (filled($icono) && $icono !== 'enlace') {
            return $icono;
        }

        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));
        foreach (self::DOMINIOS as $dominio => $red) {
            if ($host === $dominio || str_ends_with($host, '.'.$dominio)) {
                return $red;
            }
        }

        return $icono ?: null;
    }
}
