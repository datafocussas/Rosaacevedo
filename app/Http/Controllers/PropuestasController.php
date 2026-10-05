<?php

namespace App\Http\Controllers;

use App\Models\Eje;
use App\Models\Noticia;
use App\Models\PropuestaCiudadana;

class PropuestasController extends Controller
{
    public function index()
    {
        return view('sitio.propuestas', ['ejes' => Eje::query()->publicados()->with('media')->get()]);
    }

    public function eje(Eje $eje)
    {
        abort_unless($eje->publicado, 404);

        return view('sitio.eje', [
            'eje' => $eje,
            'otros' => Eje::query()->publicados()->where('id', '!=', $eje->id)->get(),
            'noticias' => Noticia::query()->publicadas()->where('eje_id', $eje->id)->with(['eje', 'media'])->limit(3)->get(),
            // Propuestas ciudadanas destacadas del tema, anónimas y con autorización para publicar (RF-23).
            'destacadas' => PropuestaCiudadana::query()->where('destacada', true)->where('publicar_anonima', true)
                ->whereHas('tema', fn ($q) => $q->where('eje_id', $eje->id))->with('barrio.comuna')->latest()->limit(4)->get(),
            'temaId' => $eje->temas()->where('activo', true)->value('id'),
        ]);
    }
}
