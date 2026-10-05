<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Días hábiles en Colombia (lunes a viernes sin festivos, Ley 51 de 1983) para los plazos
 * de las solicitudes del titular.
 */
final class DiasHabiles
{
    private static array $festivos = [];

    public static function sumar(\DateTimeInterface $desde, int $dias): Carbon
    {
        $fecha = Carbon::instance($desde)->startOfDay();

        while ($dias > 0) {
            $fecha->addDay();
            if (self::esHabil($fecha)) {
                $dias--;
            }
        }

        return $fecha;
    }

    public static function entre(\DateTimeInterface $desde, \DateTimeInterface $hasta): int
    {
        $fecha = Carbon::instance($desde)->startOfDay();
        $fin = Carbon::instance($hasta)->startOfDay();
        $dias = 0;

        while ($fecha->lt($fin)) {
            $fecha->addDay();
            if (self::esHabil($fecha)) {
                $dias++;
            }
        }

        return $dias;
    }

    public static function esHabil(CarbonInterface $fecha): bool
    {
        return ! $fecha->isWeekend() && ! in_array($fecha->format('Y-m-d'), self::festivos($fecha->year), true);
    }

    /** Festivos de Colombia para un año. */
    public static function festivos(int $anio): array
    {
        if (isset(self::$festivos[$anio])) {
            return self::$festivos[$anio];
        }

        $fijos = ['01-01', '05-01', '07-20', '08-07', '12-08', '12-25'];
        // Se trasladan al lunes siguiente (Ley Emiliani).
        $trasladables = ['01-06', '03-19', '06-29', '08-15', '10-12', '11-01', '11-11'];

        $lista = [];
        foreach ($fijos as $md) {
            $lista[] = "$anio-$md";
        }
        foreach ($trasladables as $md) {
            $lista[] = self::lunesSiguiente(Carbon::parse("$anio-$md"))->format('Y-m-d');
        }

        $pascua = self::domingoDePascua($anio);
        $lista[] = $pascua->copy()->subDays(3)->format('Y-m-d'); // Jueves santo
        $lista[] = $pascua->copy()->subDays(2)->format('Y-m-d'); // Viernes santo
        $lista[] = self::lunesSiguiente($pascua->copy()->addDays(39))->format('Y-m-d'); // Ascensión
        $lista[] = self::lunesSiguiente($pascua->copy()->addDays(60))->format('Y-m-d'); // Corpus Christi
        $lista[] = self::lunesSiguiente($pascua->copy()->addDays(68))->format('Y-m-d'); // Sagrado Corazón

        return self::$festivos[$anio] = $lista;
    }

    /** Algoritmo de Meeus/Jones/Butcher (sin depender de la extensión calendar). */
    private static function domingoDePascua(int $anio): Carbon
    {
        $a = $anio % 19;
        $b = intdiv($anio, 100);
        $c = $anio % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($anio, $mes, $dia)->startOfDay();
    }

    private static function lunesSiguiente(Carbon $fecha): Carbon
    {
        return $fecha->isMonday() ? $fecha : $fecha->next(CarbonInterface::MONDAY);
    }
}
