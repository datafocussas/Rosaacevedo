<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\PropuestaCiudadana;
use App\Models\TerritorioComuna;
use App\Services\Ajustes;

/** Páginas territoriales (RF-14). Una plantilla común con el contenido propio de cada comuna. */
class ComunasController extends Controller
{
    public function index()
    {
        return view('sitio.comunas', ['comunas' => SitioController::comunasConConteo()]);
    }

    public function show(TerritorioComuna $comuna, Ajustes $ajustes)
    {
        abort_unless($comuna->division === '2024', 404);

        $pagina = $comuna->pagina;
        $codigo = $pagina?->codigo_whatsapp ?: $comuna->codigo;

        return view('sitio.comuna', [
            'comuna' => $comuna,
            'pagina' => $pagina?->publicada ? $pagina : null,
            'comunas' => SitioController::comunasConConteo(),
            'banners' => Banner::query()->vigentes()->where('pagina', 'comuna')->where('comuna_id', $comuna->id)->with('media')->get(),
            'noticias' => Noticia::query()->publicadas()->whereHas('comunas', fn ($q) => $q->whereKey($comuna->id))->with(['eje', 'media'])->limit(3)->get(),
            'eventos' => Evento::query()->proximos()->where('comuna_id', $comuna->id)->with('comuna')->limit(3)->get(),
            'destacadas' => PropuestaCiudadana::query()->where('destacada', true)->where('publicar_anonima', true)
                ->whereHas('barrio', fn ($q) => $q->where('comuna_2024_id', $comuna->id))->with('tema')->latest()->limit(4)->get(),
            'whatsapp' => $ajustes->enlaceWhatsapp($codigo),
            'barrios' => $comuna->barrios()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }
}
