<?php

namespace Database\Seeders;

use App\Models\EnlaceBio;
use Illuminate\Database\Seeder;

class EnlacesBioSeeder extends Seeder
{
    public function run(): void
    {
        if (EnlaceBio::query()->exists()) {
            return;
        }

        $enlaces = [
            ['Conoce las propuestas', '/propuestas?utm_source=bio', 'sprout'],
            ['Deja tu propuesta para tu barrio', '/buzon?utm_source=bio', 'arrow-right'],
            ['Sigue el canal de WhatsApp', 'https://whatsapp.com/channel/0029Vb6auV20QeanaeczZw2g', 'whatsapp'],
        ];

        foreach ($enlaces as $orden => [$texto, $url, $icono]) {
            EnlaceBio::query()->create(compact('texto', 'url', 'icono') + ['orden' => $orden + 1, 'activo' => true]);
        }
    }
}
