<?php

namespace Database\Seeders;

use App\Models\TerritorioComuna;
use Illuminate\Database\Seeder;

/**
 * División 2024 (Acuerdo 017): siete comunas + corregimiento El Manzanillo, como en schema-mysql.sql.
 * Los barrios (84 + 6 sectores + 8 veredas) y la división 2007 se cargan con
 * `php artisan territorio:importar` desde el catálogo corregido del CRM.
 */
class TerritorioSeeder extends Seeder
{
    public function run(): void
    {
        $comunas = [
            ['C01', 'Comuna 1 · Centro', 'comuna', 'comuna-1-centro', 279],
            ['C02', 'Comuna 2 · Yarumito · Santa Ana', 'comuna', 'comuna-2', 196],
            ['C03', 'Comuna 3 · Ditaires · San Francisco', 'comuna', 'comuna-3', 282],
            ['C04', 'Comuna 4 · Santa María', 'comuna', 'comuna-4-santa-maria', 320],
            ['C05', 'Comuna 5 · Calatrava · El Tablazo', 'comuna', 'comuna-5', 62],
            ['C06', 'Comuna 6 · Fátima · La Unión', 'comuna', 'comuna-6', 79],
            ['C07', 'Comuna 7 · Del Valle · El Porvenir', 'comuna', 'comuna-7', 154],
            ['CORR', 'Corregimiento El Manzanillo', 'corregimiento', 'el-manzanillo', 593],
        ];

        foreach ($comunas as [$codigo, $nombre, $tipo, $slug, $area]) {
            TerritorioComuna::query()->updateOrCreate(
                ['division' => '2024', 'codigo' => $codigo],
                ['nombre' => $nombre, 'tipo' => $tipo, 'slug' => $slug, 'area_ha' => $area],
            );
        }
    }
}
