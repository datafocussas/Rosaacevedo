<?php

namespace App\Http\Controllers;

use App\Models\Noticia;

class NoticiasController extends Controller
{
    public function index()
    {
        return view('sitio.noticias', [
            'noticias' => Noticia::query()->publicadas()->with(['eje', 'media'])->paginate(12),
        ]);
    }

    public function show(Noticia $noticia)
    {
        abort_unless(Noticia::query()->publicadas()->whereKey($noticia->id)->exists(), 404);

        return $this->mostrar($noticia);
    }

    public function vistaPrevia(Noticia $noticia)
    {
        return $this->mostrar($noticia, true)->header('X-Robots-Tag', 'noindex');
    }

    private function mostrar(Noticia $noticia, bool $vistaPrevia = false)
    {
        $noticia->load(['eje', 'comunas', 'media', 'autor']);
        $imagen = $noticia->getFirstMedia('destacada');

        return response()->view('sitio.noticia', [
            'noticia' => $noticia,
            'vistaPrevia' => $vistaPrevia,
            'relacionadas' => Noticia::query()->publicadas()->where('id', '!=', $noticia->id)
                ->when($noticia->eje_id, fn ($q) => $q->where('eje_id', $noticia->eje_id))->with(['eje', 'media'])->limit(3)->get(),
            'imagenRedes' => $imagen ? ($imagen->hasGeneratedConversion('og') ? $imagen->getUrl('og') : $imagen->getUrl()) : null,
            'datosEstructurados' => [
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $noticia->titulo,
                'description' => $noticia->resumen,
                'datePublished' => $noticia->publicada_en?->toIso8601String(),
                'dateModified' => $noticia->updated_at?->toIso8601String(),
                'image' => $imagen ? [url($imagen->getUrl())] : [],
                'author' => ['@type' => 'Organization', 'name' => 'Campaña Rosa Acevedo', 'url' => url('/')],
                'publisher' => ['@type' => 'Organization', 'name' => 'Rosa Acevedo', 'url' => url('/')],
                'mainEntityOfPage' => route('noticias.show', $noticia),
            ],
        ]);
    }
}
