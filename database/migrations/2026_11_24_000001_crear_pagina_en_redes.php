<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Deja lista, en borrador, la página /en-redes con el bloque de perfiles y publicaciones de redes sociales.
// Comunicaciones la revisa, agrega publicaciones destacadas y la publica (y la pone en el menú) desde el panel.
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('paginas')->where('slug', 'en-redes')->exists()) {
            return;
        }

        DB::table('paginas')->insert([
            'slug' => 'en-redes',
            'titulo' => 'En redes',
            'estado' => 'borrador',
            'seo_descripcion' => 'Lo que pasa en las redes de Rosa Acevedo: Facebook, Instagram y TikTok.',
            'bloques' => json_encode([[
                'type' => 'redes_widgets',
                'data' => [
                    'activo' => true,
                    'fondo' => 'arena',
                    'etiqueta' => 'En redes',
                    'titulo' => 'Lo que pasa en nuestras redes',
                    'texto' => null,
                    'perfiles' => ['instagram', 'tiktok', 'facebook'],
                    'publicaciones' => [],
                ],
            ]], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('paginas')->where('slug', 'en-redes')->where('estado', 'borrador')->delete();
    }
};
