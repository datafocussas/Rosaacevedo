<?php

namespace App\Services\Asistente;

use App\Models\ConocimientoAsistente;
use App\Models\Eje;

/**
 * Arma el texto que el asistente usa como única fuente: las fichas activas del panel (Asistente → Base de
 * conocimiento) y, si está activado, los ejes publicados con sus compromisos. Va completo en cada pregunta (con
 * caché de prompts de Anthropic), así que no se recorta: el panel avisa si crece demasiado.
 */
class BaseConocimiento
{
    /** Tamaño a partir del cual el panel advierte (unos 150 000 tokens: respuestas lentas y costosas). */
    public const AVISO_CARACTERES = 600_000;

    public function __construct(private AsistenteConfig $config) {}

    public function texto(): string
    {
        $partes = [];

        foreach (ConocimientoAsistente::query()->activas()->get() as $ficha) {
            $encabezado = '### '.$ficha->titulo.' ('.(ConocimientoAsistente::CATEGORIAS[$ficha->categoria] ?? $ficha->categoria).')';
            $fuente = $ficha->fuente ? "\nFuente: ".$ficha->fuente : '';
            $partes[] = $encabezado.$fuente."\n".trim(self::plano($ficha->contenido));
        }

        if ($this->config->get('incluir_ejes')) {
            foreach (Eje::query()->publicados()->get() as $eje) {
                $lineas = ['### '.$eje->titulo().' (eje de propuestas publicado en '.route('propuestas.eje', $eje, false).')'];
                if ($eje->frase) {
                    $lineas[] = $eje->frase;
                }
                foreach ((array) $eje->compromisos as $compromiso) {
                    if (filled($compromiso['texto'] ?? null)) {
                        $lineas[] = '- Compromiso: '.trim($compromiso['texto']);
                    }
                }
                foreach (self::textosDeBloques((array) $eje->contenido) as $texto) {
                    $lineas[] = $texto;
                }
                $partes[] = implode("\n", $lineas);
            }
        }

        return implode("\n\n", $partes);
    }

    /** Tokens aproximados (4 caracteres por token en español). Solo para orientar en el panel. */
    public function tokensAproximados(): int
    {
        return (int) ceil(mb_strlen($this->texto()) / 4);
    }

    /** HTML del editor a texto plano conservando párrafos y viñetas. */
    public static function plano(?string $html): string
    {
        $texto = preg_replace(['/<\s*br\s*\/?>/i', '/<\/(p|h[1-6]|li|blockquote)>/i', '/<li[^>]*>/i'], ["\n", "\n", '- '], (string) $html);
        $texto = html_entity_decode(strip_tags((string) $texto), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace("/\n{3,}/", "\n\n", preg_replace('/[ \t]+/', ' ', $texto)));
    }

    /** Títulos y textos de los bloques del constructor (contenido de los ejes). */
    private static function textosDeBloques(array $bloques): array
    {
        $textos = [];
        foreach ($bloques as $bloque) {
            if (! ($bloque['data']['activo'] ?? true)) {
                continue;
            }
            foreach (['titulo', 'texto', 'contenido'] as $campo) {
                $valor = $bloque['data'][$campo] ?? null;
                if (is_string($valor) && filled($valor)) {
                    $textos[] = self::plano($valor);
                }
            }
            foreach ((array) ($bloque['data']['cifras'] ?? []) as $cifra) {
                if (filled($cifra['valor'] ?? null)) {
                    $textos[] = trim(($cifra['valor'] ?? '').' '.($cifra['texto'] ?? ''));
                }
            }
        }

        return $textos;
    }
}
