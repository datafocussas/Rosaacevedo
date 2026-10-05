<x-layouts.sitio :titulo="$noticia->seo_titulo ?: $noticia->titulo" :descripcion="$noticia->seo_descripcion ?: $noticia->resumen" :imagen-redes="$imagenRedes" tipo-og="article" :datos-estructurados="$datosEstructurados" :no-indexar="$vistaPrevia">
    @if ($vistaPrevia)
        <div class="ra-contenedor"><div class="ra-aviso ra-aviso-alerta" role="note"><x-ra.icono nombre="alerta" /><span>Vista previa: esta noticia está en estado «{{ $noticia->estado }}».</span></div></div>
    @endif
    <article>
        <header class="ra-seccion ra-cabecera-pagina">
            <div class="ra-contenedor ra-lectura ra-pila-4">
                <a class="ra-enlace-fuerte" href="{{ route('noticias') }}">Noticias</a>
                @if ($noticia->eje)<a class="ra-chip ra-alinear-inicio" href="{{ route('propuestas.eje', $noticia->eje) }}">{{ $noticia->eje->nombreCorto() }}</a>@endif
                <h1 class="ra-display">{{ $noticia->titulo }}</h1>
                @if ($noticia->resumen)<p class="ra-cuerpo-lg ra-sin-margen">{{ $noticia->resumen }}</p>@endif
                <p class="ra-pequeno ra-sin-margen">
                    @if ($noticia->publicada_en)<time datetime="{{ $noticia->publicada_en->toIso8601String() }}">{{ $noticia->publicada_en->translatedFormat('j \d\e F \d\e Y') }}</time>@endif
                    @if ($noticia->comunas->isNotEmpty()) · {{ $noticia->comunas->map->rotulo()->join(', ') }}@endif
                </p>
            </div>
        </header>
        @php $destacada = $noticia->getFirstMedia('destacada'); @endphp
        @if ($destacada)
            <figure class="ra-contenedor ra-figura">
                <img src="{{ $destacada->hasGeneratedConversion('w1200') ? $destacada->getUrl('w1200') : $destacada->getUrl() }}" alt="{{ $destacada->getCustomProperty('alt', '') }}" width="1200" height="675" fetchpriority="high">
            </figure>
        @endif
        <div class="ra-contenedor ra-seccion-compacta">
            <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($noticia->cuerpo) }}</div>
            @if ($noticia->video_url)
                @include('partials.bloques.video', ['d' => ['url' => $noticia->video_url]])
            @endif
            @php $galeria = $noticia->getMedia('galeria'); @endphp
            @if ($galeria->isNotEmpty())
                <div class="ra-grilla ra-grilla-3 ra-galeria">
                    @foreach ($galeria as $foto)
                        <img class="ra-galeria-foto" src="{{ $foto->hasGeneratedConversion('w800') ? $foto->getUrl('w800') : $foto->getUrl() }}" alt="{{ $foto->getCustomProperty('alt', '') }}" loading="lazy" decoding="async">
                    @endforeach
                </div>
            @endif
            <div class="ra-lectura">
                <x-ra.compartir :url="route('noticias.show', $noticia)" :titulo="$noticia->titulo" />
            </div>
        </div>
    </article>
    @if ($relacionadas->isNotEmpty())
        <section class="ra-seccion" aria-labelledby="relacionadas">
            <div class="ra-contenedor">
                <h2 class="ra-h2 ra-titulo-seccion" id="relacionadas">También te puede interesar</h2>
                <div class="ra-grilla ra-grilla-3">
                    @foreach ($relacionadas as $otra)<x-ra.tarjeta-noticia :noticia="$otra" />@endforeach
                </div>
            </div>
        </section>
    @endif
    @include('partials.bloques.llamado', ['d' => []])
</x-layouts.sitio>
