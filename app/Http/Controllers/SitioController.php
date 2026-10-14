<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AsignarVariante;
use App\Models\Banner;
use App\Models\Eje;
use App\Models\EnlaceBio;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\Pagina;
use App\Models\RedSocial;
use App\Models\TerritorioComuna;
use App\Services\Ajustes;
use App\Services\TextosLegales;
use Illuminate\Http\Request;

class SitioController extends Controller
{
    public function inicio(Request $request)
    {
        $pagina = Pagina::query()->where('slug', 'inicio')->first();
        $variante = $request->attributes->get('variante');

        $banners = Banner::query()->vigentes()->where('pagina', 'inicio')->with('media')
            ->when(AsignarVariante::variantesActivas() !== [], fn ($q) => $q->where(fn ($q) => $q->whereNull('variante')->orWhere('variante', $variante)))
            ->get();

        return view('sitio.inicio', [
            'pagina' => $pagina,
            'secciones' => collect($pagina?->bloques ?? [])->filter(fn ($b) => $b['data']['activo'] ?? true),
            'banners' => $banners,
            'ejes' => Eje::query()->publicados()->get(),
            'comunas' => self::comunasConConteo(),
            'noticias' => Noticia::query()->publicadas()->with(['eje', 'media'])->limit(3)->get(),
            'eventos' => Evento::query()->proximos()->with('comuna')->limit(3)->get(),
            'datosEstructurados' => self::persona(),
        ]);
    }

    public function pagina(string $slug)
    {
        $pagina = Pagina::query()->publicadas()->where('slug', $slug)->first();

        abort_unless($pagina, 404);

        return view('sitio.pagina', [
            'pagina' => $pagina,
            'datosEstructurados' => $slug === 'conoce-a-rosa' ? self::persona() : null,
        ]);
    }

    public function vistaPrevia(Pagina $pagina)
    {
        return response()->view('sitio.pagina', ['pagina' => $pagina, 'vistaPrevia' => true])->header('X-Robots-Tag', 'noindex');
    }

    public function politicaDatos(TextosLegales $textos)
    {
        return view('sitio.politica-datos', [
            'politica' => $textos->vigente('tratamiento_datos'),
            'cookies' => $textos->vigente('cookies'),
            'autorizaciones' => collect(['autorizacion_general', 'whatsapp', 'afinidad_politica', 'publicar_propuesta'])
                ->mapWithKeys(fn ($tipo) => [$tipo => $textos->vigente($tipo)])->filter(),
        ]);
    }

    public function transparencia(Ajustes $ajustes)
    {
        // Solo en modo campaña (Ley 1475 de 2011).
        abort_if($ajustes->enPrecampana(), 404);

        return view('sitio.transparencia', ['datos' => $ajustes->get('transparencia', [])]);
    }

    public function enlaces()
    {
        return view('sitio.enlaces', ['enlaces' => EnlaceBio::query()->where('activo', true)->orderBy('orden')->get()]);
    }

    public function robots()
    {
        $contenido = app()->environment('production')
            ? "User-agent: *\nDisallow: /admin\nDisallow: /q/\nDisallow: /vista-previa/\nDisallow: /api/\n\nSitemap: ".url('/sitemap.xml')."\n"
            : "User-agent: *\nDisallow: /\n";

        return response($contenido, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function sitemap()
    {
        $urls = collect([
            ['loc' => route('inicio'), 'prioridad' => '1.0'],
            ['loc' => route('sumate'), 'prioridad' => '0.9'],
            ['loc' => route('propuestas'), 'prioridad' => '0.9'],
            ['loc' => route('buzon'), 'prioridad' => '0.8'],
            ['loc' => route('noticias'), 'prioridad' => '0.8'],
            ['loc' => route('agenda'), 'prioridad' => '0.6'],
            ['loc' => route('comunas'), 'prioridad' => '0.6'],
            ['loc' => route('enlaces'), 'prioridad' => '0.3'],
            ['loc' => route('politica-de-datos'), 'prioridad' => '0.3'],
            ['loc' => route('mis-datos'), 'prioridad' => '0.3'],
        ]);

        Pagina::query()->publicadas()->where('slug', '!=', 'inicio')->get()
            ->each(fn ($p) => $urls->push(['loc' => url($p->ruta()), 'modificada' => $p->updated_at, 'prioridad' => '0.7']));
        Eje::query()->publicados()->get()
            ->each(fn ($e) => $urls->push(['loc' => route('propuestas.eje', $e), 'modificada' => $e->updated_at, 'prioridad' => '0.7']));
        TerritorioComuna::query()->vigente()->whereHas('pagina', fn ($q) => $q->where('publicada', true))->get()
            ->each(fn ($c) => $urls->push(['loc' => route('comunas.show', $c), 'prioridad' => '0.6']));
        Noticia::query()->publicadas()->get(['slug', 'updated_at'])
            ->each(fn ($n) => $urls->push(['loc' => route('noticias.show', $n), 'modificada' => $n->updated_at, 'prioridad' => '0.6']));

        return response()->view('sitio.sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /** Comunas 2024 con el conteo de barrios, sectores y veredas del catálogo cargado. */
    public static function comunasConConteo()
    {
        return TerritorioComuna::query()->vigente()->with('pagina')->withCount([
            'barrios' => fn ($q) => $q->where('activo', true)->where('tipo', 'barrio'),
            'barrios as sectores_count' => fn ($q) => $q->where('activo', true)->where('tipo', 'sector'),
            'barrios as veredas_count' => fn ($q) => $q->where('activo', true)->where('tipo', 'vereda'),
        ])->get();
    }

    /** Datos estructurados Person (RNF-06). Solo datos confirmados. */
    public static function persona(): array
    {
        $redes = RedSocial::query()->where('activa', true)->pluck('url')->all();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => 'Rosa María Acevedo Jaramillo',
            'alternateName' => 'Rosa Acevedo',
            'url' => url('/'),
            'sameAs' => $redes,
            'homeLocation' => ['@type' => 'City', 'name' => 'Itagüí', 'address' => ['@type' => 'PostalAddress', 'addressRegion' => 'Antioquia', 'addressCountry' => 'CO']],
        ];
    }
}
