<?php

namespace App\Console\Commands;

use App\Models\TerritorioBarrio;
use App\Models\TerritorioComuna;
use App\Support\CacheSitio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga el catálogo corregido de barrios, sectores y veredas (Acuerdo 017 de 2024) con su
 * correspondencia 2007, desde un CSV exportado del CRM:
 *
 *   nombre,tipo,comuna_2024,comuna_2007,codigo_externo
 *   Santa María 1,barrio,C04,C04,C04-SM1
 *
 * tipo: barrio | sector | vereda. comuna_2024: C01…C07 o CORR. comuna_2007: código de la división anterior.
 */
class ImportarTerritorio extends Command
{
    protected $signature = 'territorio:importar {archivo : Ruta del CSV} {--simular : Valida sin guardar}';

    protected $description = 'Importa barrios, sectores y veredas con su comuna 2024 y 2007.';

    public function handle(): int
    {
        $ruta = $this->argument('archivo');
        if (! is_readable($ruta)) {
            $this->error("No se puede leer {$ruta}.");

            return self::FAILURE;
        }

        $archivo = fopen($ruta, 'r');
        $encabezado = array_map(fn ($c) => trim(strtolower(preg_replace('/^\xEF\xBB\xBF/', '', $c))), fgetcsv($archivo));
        $esperado = ['nombre', 'tipo', 'comuna_2024', 'comuna_2007', 'codigo_externo'];
        if (array_diff($esperado, $encabezado)) {
            $this->error('Encabezado esperado: '.implode(',', $esperado));

            return self::FAILURE;
        }

        $comunas2024 = TerritorioComuna::query()->where('division', '2024')->pluck('id', 'codigo');
        $filas = [];
        $errores = [];
        $linea = 1;

        while (($datos = fgetcsv($archivo)) !== false) {
            $linea++;
            if (count(array_filter($datos)) === 0) {
                continue;
            }
            $fila = array_combine($encabezado, array_pad($datos, count($encabezado), null));
            $fila = array_map(fn ($v) => $v === null ? null : trim($v), $fila);

            if (! in_array($fila['tipo'], ['barrio', 'sector', 'vereda'], true)) {
                $errores[] = "Línea {$linea}: tipo «{$fila['tipo']}» no válido.";
            }
            if (! isset($comunas2024[$fila['comuna_2024']])) {
                $errores[] = "Línea {$linea}: comuna 2024 «{$fila['comuna_2024']}» no existe.";
            }
            if ($fila['nombre'] === '') {
                $errores[] = "Línea {$linea}: falta el nombre.";
            }
            $filas[] = $fila;
        }
        fclose($archivo);

        if ($errores) {
            foreach ($errores as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $resumen = collect($filas)->countBy('tipo');
        $this->info(count($filas).' filas válidas: '.$resumen->map(fn ($n, $t) => "$n $t")->join(', ').'.');

        if ($this->option('simular')) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($filas, $comunas2024) {
            foreach ($filas as $fila) {
                $comuna2007 = null;
                if (filled($fila['comuna_2007'])) {
                    $comuna2007 = TerritorioComuna::query()->firstOrCreate(
                        ['division' => '2007', 'codigo' => $fila['comuna_2007']],
                        [
                            'nombre' => $fila['comuna_2007'] === 'CORR' ? 'Corregimiento (división 2007)' : 'Comuna '.ltrim(substr($fila['comuna_2007'], 1), '0').' (división 2007)',
                            'tipo' => $fila['comuna_2007'] === 'CORR' ? 'corregimiento' : 'comuna',
                        ],
                    )->id;
                }

                TerritorioBarrio::query()->updateOrCreate(
                    ['comuna_2024_id' => $comunas2024[$fila['comuna_2024']], 'nombre' => $fila['nombre']],
                    ['tipo' => $fila['tipo'], 'comuna_2007_id' => $comuna2007, 'codigo_externo' => $fila['codigo_externo'] ?: null, 'activo' => true],
                );
            }
        });

        CacheSitio::limpiar();
        $this->info('Catálogo territorial cargado.');

        return self::SUCCESS;
    }
}
