@props(['banners', 'corta' => false, 'h1' => true])
{{-- Entrada (diseño v2, §6.2). Noche sólida; foto del banner a sangre a la derecha con fundidos obligatorios (nunca
     hay texto sobre la parte visible de la foto); rosa v3 delante, siempre; nombre de Rosa abajo a la derecha.
     Slot «formulario»: cápsula de registro (solo inicio). «corta»: cabecera de comuna de 420 px, sin cápsula ni rosa.
     Varios banners activos forman un carrusel: cambian foto, textos y botones; la rosa y la cápsula se quedan. --}}
@php
    $total = $banners->count();
    $conFormulario = isset($formulario) && $formulario->isNotEmpty();
    $conFoto = $banners->contains(fn ($b) => $b->getFirstMedia('escritorio'));
@endphp
<section {{ $attributes->merge(['class' => 'ra-entrada ra-fondo-noche ra-oscuro'.($corta ? ' ra-entrada-corta' : '')]) }}
    aria-roledescription="carrusel" aria-label="Destacados" @if ($total > 1) x-data="{ actual: 0 }" @endif>
    <div class="ra-contenedor">
        <div class="ra-entrada-rejilla">
            <div class="ra-entrada-textos">
                @foreach ($banners as $i => $banner)
                    <div class="ra-entrada-texto" role="group" aria-roledescription="diapositiva" aria-label="{{ $i + 1 }} de {{ $total }}"
                        @if ($total > 1) x-show="actual === {{ $i }}" x-transition:enter.opacity.duration.400ms @if ($i > 0) x-cloak @endif @endif>
                        @if ($banner->etiqueta)<span class="ra-etiqueta">{{ $banner->etiqueta }}</span>@endif
                        @php $etiquetaTitulo = $h1 && $i === 0 ? 'h1' : 'p'; @endphp
                        @if ($banner->mostrar_lema)
                            <x-ra.lema :rosa="false" :nivel="$etiquetaTitulo" />
                            <p class="ra-bajada">{{ $banner->titular }}</p>
                        @else
                            <{{ $etiquetaTitulo }} class="ra-titular-banner">{{ $banner->titular }}</{{ $etiquetaTitulo }}>
                        @endif
                        @if ($banner->texto)<p class="ra-texto-secundario">{{ $banner->texto }}</p>@endif
                    </div>
                @endforeach
            </div>

            <div class="ra-entrada-media">
                @foreach ($banners as $i => $banner)
                    @php
                        $escritorio = $banner->getFirstMedia('escritorio');
                        $movil = $banner->getFirstMedia('movil') ?? $escritorio;
                    @endphp
                    @if ($escritorio)
                        <div class="ra-entrada-foto" @if ($total > 1) x-show="actual === {{ $i }}" x-transition:enter.opacity.duration.400ms @if ($i > 0) x-cloak @endif @endif>
                            <picture>
                                @if ($movil->hasGeneratedConversion('w800'))
                                    <source media="(max-width: 979px)" type="image/webp" srcset="{{ $movil->getUrl('w400') }} 400w, {{ $movil->getUrl('w800') }} 800w" sizes="290px">
                                @else
                                    <source media="(max-width: 979px)" srcset="{{ $movil->getUrl() }}">
                                @endif
                                @if ($escritorio->hasGeneratedConversion('w1600'))
                                    <source media="(min-width: 980px)" type="image/webp" srcset="{{ $escritorio->getUrl('w1200') }} 1200w, {{ $escritorio->getUrl('w1600') }} 1600w" sizes="55vw">
                                @endif
                                <img src="{{ $escritorio->getUrl() }}" alt="{{ $banner->alt }}" width="1600" height="900"
                                    style="object-position: {{ \App\Support\Medios::foco($escritorio) }}"
                                    @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                            </picture>
                            @unless ($corta)
                                <span class="ra-entrada-nombre" aria-hidden="true">Rosa María Acevedo Jaramillo</span>
                            @endunless
                        </div>
                    @endif
                @endforeach
                @unless ($corta)
                    @if ($conFoto)
                        <x-ra.rosa-raices class="ra-entrada-rosa" :decorativa="true" />
                    @else
                        <x-ra.rosa-raices class="ra-entrada-rosa ra-entrada-rosa-sola" :prioridad="true" />
                    @endif
                @endunless
            </div>

            @if ($total > 1)
                <div class="ra-entrada-puntos ra-puntos" role="group" aria-label="Elegir destacado">
                    @foreach ($banners as $i => $banner)
                        <button type="button" class="ra-punto" aria-current="{{ $i === 0 ? 'true' : 'false' }}" :aria-current="(actual === {{ $i }}).toString()" @click="actual = {{ $i }}" aria-label="Ver destacado {{ $i + 1 }}"></button>
                    @endforeach
                </div>
            @endif

            @if ($conFormulario)
                <div class="ra-entrada-capsula">{{ $formulario }}</div>
            @endif

            <div class="ra-entrada-acciones">
                @foreach ($banners as $i => $banner)
                    @if (($banner->btn1_texto && $banner->btn1_url) || ($banner->btn2_texto && $banner->btn2_url))
                        <div class="ra-acciones" @if ($total > 1) x-show="actual === {{ $i }}" @if ($i > 0) x-cloak @endif @endif>
                            @if ($banner->btn1_texto && $banner->btn1_url)
                                <x-ra.boton :href="$banner->btn1_url" icono="arrow-right" data-umami-event="banner_boton" data-umami-event-variante="{{ $banner->variante }}">{{ $banner->btn1_texto }}</x-ra.boton>
                            @endif
                            @if ($banner->btn2_texto && $banner->btn2_url)
                                <x-ra.boton :href="$banner->btn2_url" variante="fantasma">{{ $banner->btn2_texto }}</x-ra.boton>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
