<?php

namespace App\Services\Asistente;

use RuntimeException;
use Smalot\PdfParser\Parser;
use ZipArchive;

/** Texto de un documento para la base de conocimiento: PDF, Word (.docx), texto o Markdown. */
class ExtraerTexto
{
    public const TIPOS = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'text/markdown',
        'text/x-markdown',
    ];

    public static function desde(string $ruta, ?string $nombre = null): string
    {
        $extension = strtolower(pathinfo($nombre ?? $ruta, PATHINFO_EXTENSION));

        $texto = match ($extension) {
            'pdf' => (new Parser)->parseFile($ruta)->getText(),
            'docx' => self::docx($ruta),
            'txt', 'md', 'markdown' => (string) file_get_contents($ruta),
            default => throw new RuntimeException('Formato no admitido: usa PDF, Word (.docx), .txt o .md.'),
        };

        $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8');
        $texto = preg_replace("/[ \t]+/u", ' ', $texto);
        $texto = trim(preg_replace("/\n{3,}/u", "\n\n", str_replace("\r", '', $texto)));

        if ($texto === '') {
            throw new RuntimeException('El documento no tiene texto que se pueda leer (¿es una imagen escaneada?). Copia el texto a mano.');
        }

        return $texto;
    }

    private static function docx(string $ruta): string
    {
        $zip = new ZipArchive;
        if ($zip->open($ruta) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo de Word.');
        }
        $xml = (string) $zip->getFromName('word/document.xml');
        $zip->close();

        $xml = preg_replace(['/<w:tab\/>/', '/<\/w:p>/', '/<w:br[^>]*\/>/'], ["\t", "\n", "\n"], $xml);

        return html_entity_decode(strip_tags((string) $xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
