<?php

use App\Models\Politica;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// La clasificación de propuestas con IA (RF-22) aún no funciona, así que el sitio deja de anunciarla:
// se archiva la página /uso-de-ia, se quita del menú y la política de datos pasa a una versión nueva sin
// la sección «Inteligencia artificial» (la versión anterior queda intacta como evidencia de lo aceptado).
// Si más adelante se activa, la Circular SIC 002 de 2026 obliga a volver a informarlo.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('paginas')->where('slug', 'uso-de-ia')->update(['estado' => 'archivada']);
        DB::table('menu_items')->where('url', '/uso-de-ia')->delete();
        DB::table('politicas')->where('tipo', 'uso_ia')->update(['vigente' => false]);

        $vigente = Politica::vigente('tratamiento_datos');
        if ($vigente && str_contains($vigente->texto, '## Inteligencia artificial')) {
            $texto = preg_replace('/\n## Inteligencia artificial\n.*?(?=\n## )/s', '', $vigente->texto);
            $texto = str_replace('- Leer, clasificar y responder tus propuestas.', '- Leer, clasificar y responder tus propuestas. Una persona del equipo las revisa.', $texto);
            $mayor = Politica::query()->where('tipo', 'tratamiento_datos')->pluck('version')
                ->map(fn ($v) => (float) $v)->max();

            $nueva = Politica::query()->create([
                'tipo' => 'tratamiento_datos',
                'version' => number_format($mayor + 0.1, 1, '.', ''),
                'texto' => $texto,
                'vigente_desde' => now(),
                'vigente' => false,
            ]);
            $nueva->activar();
        }
    }

    public function down(): void
    {
        // No se revierte: las versiones de políticas son de solo avance.
    }
};
