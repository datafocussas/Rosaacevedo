@php $destacada = $noticia->getFirstMedia('destacada'); @endphp
<x-layouts.sitio :titulo="$noticia->seo_titulo ?: $noticia->titulo" :descripcion="$noticia->seo_descripcion ?: $noticia->resumen" :imagen-redes="$imagenRedes" tipo-og="article" :datos-estructurados="$datosEstructurados" :no-indexar="$vistaPrevia">
    <article>
        <x-ra.cabecera :titulo="$noticia->titulo" :entradilla="$noticia->resumen"
            :imagen="\App\Support\Medios::url($destacada, 'w1600')" :alt="$destacada?->getCustomProperty('alt', '') ?? ''" :foco="$destacada ? \App\Support\Medios::foco($destacada, [50, 30]) : null">
            <x-slot:antes>
                @if ($vistaPrevia)
                    <div class="ra-aviso ra-aviso-alerta" role="note"><x-ra.icono nombre="alerta" /><span>Vista previa: esta noticia está en estado «{{ $noticia->estado }}».</span></div>
                @endif
                <a class="ra-volver" href="{{ route('noticias') }}">Noticias</a>
                <p class="ra-noticia-meta ra-sin-margen">
                    @if ($noticia->eje)<a class="ra-noticia-cat" href="{{ route('propuestas.eje', $noticia->eje) }}">{{ $noticia->eje->nombreCorto() }}</a><span aria-hidden="true">·</span>@endif
                    @if ($noticia->publicada_en)<time datetime="{{ $noticia->publicada_en->toIso8601String() }}">{{ $noticia->publicada_en->translatedFormat('j \d\e F \d\e Y') }}</time>@endif
                    @if ($noticia->comunas->isNotEmpty())<span aria-hidden="true">·</span> {{ $noticia->comunas->map->rotulo()->join(', ') }}@endif
                </p>
            </x-slot:antes>
        </x-ra.cabecera>
        <div class="ra-seccion ra-fondo-marfil">
            <div class="ra-contenedor">
                <div class="ra-lectura-centrada">
                    <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($noticia->cuerpo) }}</div>
                    @php $galeria = $noticia->getMedia('galeria'); @endphp
                    @if ($galeria->isNotEmpty())
                        <div class="ra-galeria ra-galeria-noticia">
                            @foreach ($galeria as $foto)
                                <img src="{{ \App\Support\Medios::url($foto, 'w800') }}" alt="{{ $foto->getCustomProperty('alt', '') }}" loading="lazy" decoding="async">
                            @endforeach
                        </div>
                    @endif
                    <x-ra.compartir :url="route('noticias.show', $noticia)" :titulo="$noticia->titulo" />
                </div>
            </div>
        </div>
        @if ($noticia->video_url)
            @include('partials.bloques.video', ['d' => ['url' => $noticia->video_url], 'clasesFondo' => 'ra-fondo-noche ra-oscuro'])
        @endif
    </article>
    @if ($relacionadas->isNotEmpty())
        <section class="ra-seccion ra-fondo-blanco" aria-labelledby="relacionadas">
            <div class="ra-contenedor ra-pila-5">
                <h2 class="ra-h2-medio" id="relacionadas">También te puede interesar</h2>
                <div class="ra-noticias-rejilla">
                    @foreach ($relacionadas as $otra)<x-ra.tarjeta-noticia :noticia="$otra" />@endforeach
                </div>
            </div>
        </section>
    @endif
    @include('partials.bloques.llamado', ['d' => [], 'clasesFondo' => 'ra-fondo-esmeralda ra-oscuro'])
</x-layouts.sitio>
