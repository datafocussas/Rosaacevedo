<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Datos semilla del Sprint 0: territorio, ejes, redes, ajustes, menú, temas, roles, textos y páginas base. */
    public function run(): void
    {
        $this->call([
            TerritorioSeeder::class,
            EjesSeeder::class,
            TemasSeeder::class,
            AjustesSeeder::class,
            RedesSeeder::class,
            MenuSeeder::class,
            EnlacesBioSeeder::class,
            RolesSeeder::class,
            PoliticasSeeder::class,
            PaginasSeeder::class,
        ]);
    }
}
