<?php

namespace Database\Seeders;

use App\Models\Eje;
use Illuminate\Database\Seeder;

/** Los siete ejes «Aquí me planto por…». La salud va primero (pendiente de validación con Estrategia). */
class EjesSeeder extends Seeder
{
    public function run(): void
    {
        $ejes = [
            ['salud', 'la', 'salud', 'heart-pulse', 'Red de salud cercana: citas, especialistas y atención en el barrio.'],
            ['familias', 'las', 'familias', 'users', 'Que cada hogar viva mejor y con tranquilidad.'],
            ['jovenes', 'los', 'jóvenes', 'graduation-cap', 'Que ningún joven tenga que irse de Itagüí para cumplir sus sueños.'],
            ['comerciantes', 'los', 'comerciantes', 'store', 'Que el negocio de barrio vuelva a respirar.'],
            ['seguridad', 'la', 'seguridad', 'shield', 'Que el miedo deje de caminar primero.'],
            ['oportunidades', 'las', 'oportunidades', 'trending-up', 'Empleo cerca de casa y emprendimiento con respaldo.'],
            ['ciudad-que-avanza', 'una ciudad que', 'avanza', 'sprout', 'Movilidad, espacio público y una ciudad moderna.'],
        ];

        foreach ($ejes as $orden => [$slug, $articulo, $sujeto, $icono, $frase]) {
            Eje::query()->firstOrCreate(['slug' => $slug], [
                'articulo' => $articulo,
                'sujeto' => $sujeto,
                'icono' => $icono,
                'frase' => $frase,
                'orden' => $orden + 1,
                'publicado' => true,
            ]);
        }
    }
}
