<?php

namespace Tests;

use App\Models\TerritorioBarrio;
use App\Models\TerritorioComuna;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /** Datos semilla (comunas, ejes, políticas vigentes, roles…) en cada prueba con RefreshDatabase. */
    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    protected function barrio(string $nombre = 'Santa María 1', string $comuna = 'C04'): TerritorioBarrio
    {
        return TerritorioBarrio::query()->firstOrCreate(
            ['nombre' => $nombre, 'comuna_2024_id' => TerritorioComuna::query()->where('division', '2024')->where('codigo', $comuna)->value('id')],
            ['tipo' => 'barrio', 'codigo_externo' => $comuna.'-X'],
        );
    }

    protected function registrar(array $datos = []): TestResponse
    {
        return $this->postJson('/api/v1/registro', array_replace_recursive([
            'nombre' => 'Diana',
            'celular' => '300 123 4567',
            'consent' => ['general' => true, 'whatsapp' => false],
            'origen' => ['utm_source' => 'facebook', 'pagina' => '/'],
        ], $datos));
    }
}
