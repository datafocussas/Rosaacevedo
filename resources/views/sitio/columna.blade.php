@php
    $destacada = $columna->getFirstMedia('destacada');
    $columnista = $columna->columnista;
@endphp
<x-layouts.sitio :titulo="$columna->seo_titulo ?: $columna->titulo" :descripcion="$columna->seo_descripcion ?: $columna->resumen" :imagen-redes="$imagenRedes" tipo-og="article" :datos-estructurados="$datosEstructurados" :no-indexar="$vistaPrevia">
    <article>
        <x-ra.cabecera etiqueta="Opinión" :titulo="$columna->titulo" :entradilla="$columna->resumen"
            :imagen="\App\Support\Medios::url($destacada, 'w1600')" :alt="$destacada?->getCustomProperty('alt', '') ?? ''" :foco="$destacada ? \App\Support\Medios::foco($destacada, [50, 30]) : null">
            <x-slot:antes>
                @if ($vistaPrevia)
                    <div class="ra-aviso ra-aviso-alerta" role="note"><x-ra.icono nombre="alerta" /><span>Vista previa: esta columna está en estado «{{ $columna->estado }}».</span></div>
                @endif
                <a class="ra-volver" href="{{ route('opinion') }}">Opinión</a>
            </x-slot:antes>
            <div class="ra-columna-firma">
                <x-ra.avatar-columnista :columnista="$columnista" />
                <div>
                    <p class="ra-columna-autor ra-sin-margen">{{ $columnista->nombre }}</p>
                    <p class="ra-columna-meta ra-sin-margen">
                        @if ($columna->publicada_en)<time datetime="{{ $columna->publicada_en->toIso8601String() }}">{{ $columna->publicada_en->translatedFormat('j \d\e F \d\e Y') }}</time><span aria-hidden="true"> · </span>@endif
                        <span>{{ $columna->minutosLectura() }} min de lectura</span>
                    </p>
                </div>
            </div>
        </x-ra.cabecera>
        <div class="ra-seccion ra-fondo-marfil">
            <div class="ra-contenedor">
                <div class="ra-lectura-centrada ra-pila-5">
                    <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($columna->cuerpo) }}</div>
                    @php $galeria = $columna->getMedia('galeria'); @endphp
                    @if ($galeria->isNotEmpty())
                        <div class="ra-galeria ra-galeria-noticia">
                            @foreach ($galeria as $foto)
                                <img src="{{ \App\Support\Medios::url($foto, 'w800') }}" alt="{{ $foto->getCustomProperty('alt', '') }}" loading="lazy" decoding="async">
                            @endforeach
                        </div>
                    @endif
                    <aside class="ra-columna-bio" aria-label="Sobre quien escribe">
                        <x-ra.avatar-columnista :columnista="$columnista" :tamano="72" />
                        <div class="ra-pila">
                            <p class="ra-columna-autor ra-sin-margen">{{ $columnista->nombre }}</p>
                            @if ($columnista->perfil)<p class="ra-texto-secundario ra-sin-margen">{{ $columnista->perfil }}</p>@endif
                            @if ($columnista->biografia)<p class="ra-sin-margen">{{ $columnista->biografia }}</p>@endif
                        </div>
                    </aside>
                    <p class="ra-columna-aviso">Las columnas de opinión expresan el pensamiento de quienes las firman.</p>
                    <x-ra.compartir :url="route('opinion.show', $columna)" :titulo="$columna->titulo" />
                </div>
            </div>
        </div>
    </article>
    @if ($otras->isNotEmpty())
        <section class="ra-seccion ra-fondo-blanco" aria-labelledby="otras-columnas">
            <div class="ra-contenedor ra-pila-5">
                <h2 class="ra-h2-medio" id="otras-columnas">Más opinión</h2>
                <div class="ra-columnas-rejilla">
                    @foreach ($otras as $otra)<x-ra.tarjeta-columna :columna="$otra" />@endforeach
                </div>
            </div>
        </section>
    @endif
    @include('partials.bloques.llamado', ['d' => [], 'clasesFondo' => 'ra-fondo-esmeralda ra-oscuro'])
</x-layouts.sitio>
