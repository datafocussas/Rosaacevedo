<?php

namespace App\Support;

/**
 * Fondos de los bloques del constructor (diseño v2, §7 y §8.3).
 *
 * Cada tipo de bloque tiene fondos permitidos; el primero es el de por defecto. Como el panel permite
 * reordenar los bloques, la vista aplica la alternancia por sí sola:
 *  1. Dos bloques oscuros seguidos: el segundo usa su primer fondo claro permitido; si no tiene ninguno,
 *     se inserta un separador marfil antes de él.
 *  2. Dos bloques claros seguidos con el mismo fondo: el segundo alterna con blanco o arena (o marfil),
 *     según lo que permita.
 */
final class Fondos
{
    public const OSCUROS = ['noche', 'abismo', 'profundo', 'esmeralda', 'coral'];

    public const CLAROS = ['marfil', 'blanco', 'arena'];

    public const NOMBRES = [
        'marfil' => 'Marfil (claro)',
        'blanco' => 'Blanco (claro)',
        'arena' => 'Arena (claro)',
        'noche' => 'Noche (oscuro)',
        'abismo' => 'Abismo (oscuro)',
        'esmeralda' => 'Esmeralda (oscuro)',
        'coral' => 'Coral',
    ];

    /** Valores guardados con el diseño v1 (imagen + texto «Verde Raíces»). */
    private const HEREDADOS = ['raiz' => 'abismo'];

    /** Fondos permitidos por tipo de bloque; el primero es el de por defecto. */
    public const PERMITIDOS = [
        'registro' => ['noche'],
        'raices' => ['abismo'],
        'buzon' => ['esmeralda'],
        'redes' => ['noche'],
        'noticias' => ['marfil', 'blanco'],
        'agenda' => ['arena', 'marfil'],
        'texto' => ['marfil', 'blanco', 'arena'],
        'imagen' => ['marfil', 'blanco'],
        'imagen_texto' => ['marfil', 'arena', 'abismo'],
        'cita' => ['abismo', 'marfil'],
        'linea_tiempo' => ['marfil'],
        'video' => ['noche'],
        'galeria' => ['marfil', 'arena'],
        'cifras' => ['abismo'],
        'llamado' => ['esmeralda', 'noche', 'coral'],
        'formulario' => ['marfil'],
        'ejes' => ['marfil'],
        'comunas' => ['marfil', 'blanco'],
    ];

    public static function permitidos(string $tipo): array
    {
        return self::PERMITIDOS[$tipo] ?? ['marfil'];
    }

    public static function esOscuro(?string $fondo): bool
    {
        return in_array($fondo, self::OSCUROS, true);
    }

    /** Fondo elegido en el panel si está permitido; si no, el de por defecto. */
    public static function elegido(array $bloque): string
    {
        $permitidos = self::permitidos((string) ($bloque['type'] ?? ''));
        $elegido = $bloque['data']['fondo'] ?? null;
        $elegido = self::HEREDADOS[$elegido] ?? $elegido;

        return in_array($elegido, $permitidos, true) ? $elegido : $permitidos[0];
    }

    /** Clases CSS de una sección con este fondo. */
    public static function clases(string $fondo): string
    {
        $clase = 'ra-fondo-'.$fondo;

        return in_array($fondo, ['noche', 'abismo', 'profundo', 'esmeralda'], true) ? $clase.' ra-oscuro' : $clase;
    }

    /**
     * Resuelve los fondos de una lista de bloques visibles.
     *
     * @param  array  $bloques  Bloques del Builder (['type' => …, 'data' => […]]), ya filtrados por «visible».
     * @param  string|null  $anterior  Fondo de la sección que va justo antes de la lista (por ejemplo, la cabecera).
     * @return array<int, array{bloque: array, fondo: string, separador: bool}>
     */
    public static function resolver(array $bloques, ?string $anterior = null): array
    {
        $resultado = [];

        foreach (array_values($bloques) as $bloque) {
            $tipo = (string) ($bloque['type'] ?? '');
            $permitidos = self::permitidos($tipo);
            $fondo = self::elegido($bloque);
            $separador = false;

            if (self::esOscuro($fondo) && self::esOscuro($anterior)) {
                $claro = collect($permitidos)->first(fn ($f) => ! self::esOscuro($f));
                if ($claro) {
                    $fondo = $claro;
                } else {
                    $separador = true;
                }
            }

            if (! self::esOscuro($fondo) && $fondo === $anterior) {
                $alterno = collect(['blanco', 'arena', 'marfil'])->first(fn ($f) => $f !== $anterior && in_array($f, $permitidos, true));
                if ($alterno) {
                    $fondo = $alterno;
                }
            }

            $resultado[] = ['bloque' => $bloque, 'fondo' => $fondo, 'separador' => $separador];
            $anterior = $fondo;
        }

        return $resultado;
    }

    /** Proporción de bloques oscuros (para el aviso del panel: más del 30 %). */
    public static function proporcionOscura(array $bloques): float
    {
        $visibles = array_values(array_filter($bloques, fn ($b) => $b['data']['activo'] ?? true));

        if ($visibles === []) {
            return 0.0;
        }

        $oscuros = count(array_filter(self::resolver($visibles), fn ($r) => self::esOscuro($r['fondo'])));

        return $oscuros / count($visibles);
    }
}
