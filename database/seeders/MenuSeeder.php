<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        if (MenuItem::query()->exists()) {
            return;
        }

        $menus = [
            'principal' => [['Inicio', '/'], ['Conoce a Rosa', '/conoce-a-rosa'], ['Propuestas', '/propuestas'], ['Tu comuna', '/comunas'], ['Noticias', '/noticias'], ['Agenda', '/agenda']],
            'pie_sitio' => [['Conoce a Rosa', '/conoce-a-rosa'], ['Propuestas', '/propuestas'], ['Buzón ciudadano', '/buzon'], ['Manifiesto', '/manifiesto']],
            'pie_transparencia' => [['Política de tratamiento de datos', '/politica-de-datos'], ['Consulta o retira tus datos', '/mis-datos'], ['Uso de inteligencia artificial', '/uso-de-ia']],
        ];

        foreach ($menus as $ubicacion => $items) {
            foreach ($items as $orden => [$texto, $url]) {
                MenuItem::query()->create(compact('ubicacion', 'texto', 'url') + ['orden' => $orden + 1, 'activo' => true]);
            }
        }
    }
}
