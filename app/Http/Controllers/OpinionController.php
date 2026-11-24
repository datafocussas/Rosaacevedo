<?php

namespace App\Http\Controllers;

use App\Models\Columna;

/** Columnas de opinión: /opinion (lista) y /opinion/{columna}. */
class OpinionController extends Controller
{
    public function index()
    {
        return view('sitio.opinion', [
            'columnas' => Columna::query()->publicadas()->with(['columnista.media', 'media'])->paginate(12),
        ]);
    }

    public function show(Columna $columna)
    {
        abort_unless(Columna::query()->publicadas()->whereKey($columna->id)->exists(), 404);

        return $this->mostrar($columna);
    }

    public function vistaPrevia(Columna $columna)
    {
        return $this->mostrar($columna, true)->header('X-Robots-Tag', 'noindex');
    }

    private function mostrar(Columna $columna, bool $vistaPrevia = false)
    {
        $columna->load(['columnista.media', 'media']);
        $imagen = $columna->getFirstMedia('destacada');

        return response()->view('sitio.columna', [
            'columna' => $columna,
            'vistaPrevia' => $vistaPrevia,
            'otras' => Columna::query()->publicadas()->where('id', '!=', $columna->id)->with(['columnista.media', 'media'])->limit(3)->get(),
            'imagenRedes' => $imagen ? ($imagen->hasGeneratedConversion('og') ? $imagen->getUrl('og') : $imagen->getUrl()) : null,
            'datosEstructurados' => [
                '@context' => 'https://schema.org',
                '@type' => 'OpinionNewsArticle',
                'headline' => $columna->titulo,
                'description' => $columna->resumen,
                'datePublished' => $columna->publicada_en?->toIso8601String(),
                'dateModified' => $columna->updated_at?->toIso8601String(),
                'image' => $imagen ? [url($imagen->getUrl())] : [],
                'author' => ['@type' => 'Person', 'name' => $columna->columnista->nombre],
                'publisher' => ['@type' => 'Organization', 'name' => 'Rosa Acevedo', 'url' => url('/')],
                'mainEntityOfPage' => route('opinion.show', $columna),
            ],
        ]);
    }
}
