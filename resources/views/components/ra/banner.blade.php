@props(['banners'])
{{-- Carrusel de banners (BannerPrincipal): titular en HTML, nunca dentro de la imagen; imagen fija, no video.
     Si se pasa el slot «formulario» (página de inicio), el formulario ocupa la columna de la imagen. --}}
@php
    $total = $banners->count();
    $conFormulario = isset($formulario) && $formulario->isNotEmpty();
@endphp
<section class="ra-banner" aria-roledescription="carrusel" aria-label="Destacados" @if ($total > 1) x-data="{ actual: 0 }" @endif>
    <div class="ra-contenedor ra-seccion">
        <div class="ra-banner-grid">
            <div>
                @foreach ($banners as $i => $banner)
                    <div class="ra-banner-texto" role="group" aria-roledescription="diapositiva" aria-label="{{ $i + 1 }} de {{ $total }}"
                        @if ($total > 1) x-show="actual === {{ $i }}" @if ($i > 0) x-cloak @endif @endif>
                        @if ($banner->etiqueta)<span class="ra-etiqueta">{{ $banner->etiqueta }}</span>@endif
                        @if ($banner->mostrar_lema)<x-ra.lema />@endif
                        @if ($i === 0)
                            <h1 class="ra-display-xl ra-banner-titular">{{ $banner->titular }}</h1>
                        @else
                            <p class="ra-display-xl ra-banner-titular">{{ $banner->titular }}</p>
                        @endif
                        @if ($banner->texto)<p class="ra-cuerpo-lg ra-sin-margen">{{ $banner->texto }}</p>@endif
                        @if (($banner->btn1_texto && $banner->btn1_url) || ($banner->btn2_texto && $banner->btn2_url))
                            <div class="ra-banner-acciones">
                                @if ($banner->btn1_texto && $banner->btn1_url)
                                    <x-ra.boton :href="$banner->btn1_url" icono="arrow-right" data-umami-event="banner_boton" data-umami-event-variante="{{ $banner->variante }}">{{ $banner->btn1_texto }}</x-ra.boton>
                                @endif
                                @if ($banner->btn2_texto && $banner->btn2_url)
                                    <x-ra.boton :href="$banner->btn2_url" variante="fantasma">{{ $banner->btn2_texto }}</x-ra.boton>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
                @if ($total > 1)
                    <div class="ra-puntos" role="group" aria-label="Elegir destacado">
                        @foreach ($banners as $i => $banner)
                            <button type="button" class="ra-punto" aria-current="{{ $i === 0 ? 'true' : 'false' }}" :aria-current="(actual === {{ $i }}).toString()" @click="actual = {{ $i }}" aria-label="Ver destacado {{ $i + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
            @if ($conFormulario)
                {{ $formulario }}
            @else
                <div>
                    @foreach ($banners as $i => $banner)
                        @php
                            $escritorio = $banner->getFirstMedia('escritorio');
                            $movil = $banner->getFirstMedia('movil') ?? $escritorio;
                        @endphp
                        @if ($escritorio)
                            <picture @if ($total > 1) x-show="actual === {{ $i }}" @if ($i > 0) x-cloak @endif @endif>
                                @if ($movil->hasGeneratedConversion('w800'))
                                    <source media="(max-width: 899px)" type="image/webp" srcset="{{ $movil->getUrl('w400') }} 400w, {{ $movil->getUrl('w800') }} 800w" sizes="100vw">
                                @endif
                                @if ($escritorio->hasGeneratedConversion('w1200'))
                                    <source media="(min-width: 900px)" type="image/webp" srcset="{{ $escritorio->getUrl('w800') }} 800w, {{ $escritorio->getUrl('w1200') }} 1200w, {{ $escritorio->getUrl('w1600') }} 1600w" sizes="50vw">
                                @endif
                                <img class="ra-banner-foto" src="{{ $escritorio->getUrl() }}" alt="{{ $banner->alt }}" width="800" height="1000"
                                    @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                            </picture>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
