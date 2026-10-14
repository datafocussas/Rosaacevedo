<?php

namespace Database\Seeders;

use App\Models\RedSocial;
use Illuminate\Database\Seeder;

/** Redes oficiales. El enlace de Facebook es de compartir: reemplazarlo por la URL canónica cuando se tenga. */
class RedesSeeder extends Seeder
{
    public function run(): void
    {
        $redes = [
            ['Facebook', 'https://www.facebook.com/share/19mmqrNJD9/', 'facebook'],
            ['Instagram', 'https://www.instagram.com/rosaacevedoj/', 'instagram'],
            ['TikTok', 'https://www.tiktok.com/@rosaacevedoj', 'tiktok'],
            ['X', 'https://x.com/rosaacevedoj', 'x'],
        ];

        foreach ($redes as $orden => [$nombre, $url, $icono]) {
            RedSocial::query()->firstOrCreate(['nombre' => $nombre], ['url' => $url, 'icono' => $icono, 'orden' => $orden + 1, 'activa' => true]);
        }
    }
}
