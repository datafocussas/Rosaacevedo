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

    /** HTML del editor enriquecido, saneado (sin scripts, estilos ni atributos de eventos). */
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

        return new HtmlString(self::marcarPendientes($limpio));
    }
}
