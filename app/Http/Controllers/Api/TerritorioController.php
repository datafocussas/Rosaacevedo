<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TerritorioBarrio;
use Illuminate\Support\Facades\Cache;

class TerritorioController extends Controller
{
    /** Catálogo para selectores (caché 24 h). */
    public function barrios()
    {
        $barrios = Cache::remember('api-barrios', 86400, fn () => TerritorioBarrio::query()
            ->with('comuna')->where('activo', true)->get()
            ->sortBy([fn ($b) => $b->comuna->codigo === 'CORR' ? 'Z' : $b->comuna->codigo, 'nombre'])
            ->map(fn ($b) => [
                'id' => $b->id,
                'nombre' => $b->nombre,
                'tipo' => $b->tipo,
                'comuna' => ['id' => $b->comuna->id, 'codigo' => $b->comuna->codigo, 'nombre' => $b->comuna->nombre],
            ])->values()->all());

        return response()->json($barrios)->header('Cache-Control', 'public, max-age=86400');
    }
}
