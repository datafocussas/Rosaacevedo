<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/** QR en SVG (vectorial, para imprenta) y PNG (rasterizado con GD a partir de la matriz). */
final class CodigoQr
{
    public static function svg(string $texto, int $tamano = 1024): string
    {
        $estilo = new RendererStyle($tamano, 2, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(0, 60, 87)));

        return (new Writer(new ImageRenderer($estilo, new SvgImageBackEnd)))->writeString($texto);
    }

    public static function png(string $texto, int $modulo = 16): string
    {
        $matriz = Encoder::encode($texto, ErrorCorrectionLevel::M(), 'UTF-8')->getMatrix();
        $ancho = $matriz->getWidth();
        $margen = 2;
        $lado = ($ancho + 2 * $margen) * $modulo;

        $imagen = imagecreatetruecolor($lado, $lado);
        $blanco = imagecolorallocate($imagen, 255, 255, 255);
        $azul = imagecolorallocate($imagen, 0, 60, 87);
        imagefill($imagen, 0, 0, $blanco);

        for ($y = 0; $y < $ancho; $y++) {
            for ($x = 0; $x < $ancho; $x++) {
                if ($matriz->get($x, $y) === 1) {
                    $x0 = ($x + $margen) * $modulo;
                    $y0 = ($y + $margen) * $modulo;
                    imagefilledrectangle($imagen, $x0, $y0, $x0 + $modulo - 1, $y0 + $modulo - 1, $azul);
                }
            }
        }

        ob_start();
        imagepng($imagen);
        imagedestroy($imagen);

        return (string) ob_get_clean();
    }
}
