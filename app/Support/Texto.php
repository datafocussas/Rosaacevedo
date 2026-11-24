<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final class Texto
{
    /** Resalta los marcadores [POR CONFIRMAR] para que nadie publique un dato sin verificar sin verlo. */
    public static function marcarPendientes(string $html): string
    {
        return str_replace('[POR CONFIRMAR]', '<mark class="ra-pendiente">[POR CONFIRMAR]</mark>', $html);
    }

    /** HTML del editor enriquecido, saneado (sin scripts, estilos ni atributos de eventos), con videos y publicaciones incrustados. */
    public static function enriquecido(?string $html): HtmlString
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
            ->allowAttribute('class', '*')
            ->forceAttribute('a', 'rel', 'noopener');

        $limpio = (new HtmlSanitizer($config))->sanitize((string) $html);

        // Los enlaces pegados de videos y redes se ven incrustados (después de sanear: el marco lo arma el sitio).
        return new HtmlString(self::marcarPendientes(Incrustacion::enHtml($limpio)));
    }
}
