<?php

namespace Database\Seeders;

use App\Models\Eje;
use App\Models\Tema;
use Illuminate\Database\Seeder;

/** Catálogo inicial de temas del buzón (editable en el panel). */
class TemasSeeder extends Seeder
{
    public function run(): void
    {
        $temas = [
            ['Salud', 'salud'],
            ['Seguridad', 'seguridad'],
            ['Empleo y emprendimiento', 'oportunidades'],
            ['Movilidad', 'ciudad-que-avanza'],
            ['Jóvenes y educación', 'jovenes'],
            ['Comercio', 'comerciantes'],
            ['Familias y vivienda', 'familias'],
            ['Espacio público y medio ambiente', 'ciudad-que-avanza'],
            ['Otro', null],
        ];

        foreach ($temas as $orden => [$nombre, $eje]) {
            Tema::query()->firstOrCreate(['nombre' => $nombre], [
                'eje_id' => $eje ? Eje::query()->where('slug', $eje)->value('id') : null,
                'orden' => $orden + 1,
                'activo' => true,
            ]);
        }
    }
}
