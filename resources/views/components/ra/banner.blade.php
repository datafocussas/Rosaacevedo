@props(['banners', 'corta' => false, 'h1' => true])
{{-- Entrada (diseño v2, §6.2). Noche sólida; foto del banner a sangre a la derecha con fundidos obligatorios (nunca
     hay texto sobre la parte visible de la foto); rosa v3 delante, siempre; nombre de Rosa abajo a la derecha.
     Slot «formulario»: cápsula de registro (solo inicio). «corta»: cabecera de comuna de 420 px, sin cápsula ni rosa.
     Cada banner puede traer varias fotos: todas forman una secuencia de diapositivas que avanza sola cada «segundos_rotacion»
     del banner (carrusel.js). Al cambiar de banner cambian también textos y botones; la rosa y la cápsula se quedan.
     Con varias diapositivas hay botón de pausa (WCAG 2.2.2); con «reducir movimiento» empieza en pausa. --}}
@php
    // Diapositivas: una por foto, en orden de banner y de foto. Un banner sin fotos aporta una diapositiva sin foto.
    $diapositivas = [];
    $primera = [];
    foreach ($banners->values() as $i => $banner) {
        $primera[$i] = count($diapositivas);
        foreach ($banner->fotos() ?: [null] as $foto) {
            $diapositivas[] = ['banner' => $i, 'foto' => $foto, 'segundos' => $banner->segundos(), 'alt' => $banner->alt];
        }
    }
    $total = $banners->count();
    $animado = count($diapositivas) > 1;
    $conFormulario = isset($formulario) && $formulario->isNotEmpty();
    $conFoto = collect($diapositivas)->contains(fn ($d) => $d['foto'] !== null);
@endphp
<section {{ $attributes->merge(['class' => 'ra-entrada ra-fondo-noche ra-oscuro'.($corta ? ' ra-entrada-corta' : '')]) }}
    aria-roledescription="carrusel" aria-label="Destacados"
    @if ($animado) x-data="carrusel({{ json_encode(array_column($diapositivas, 'segundos')) }}, {{ json_encode(array_column($diapositivas, 'banner')) }})"
        @mouseenter="detener(true)" @mouseleave="detener(false)" @focusin="detener(true)" @focusout="detener(false)" @endif>
    <div class="ra-contenedor">
        <div class="ra-entrada-rejilla">
            <div class="ra-entrada-textos" @if ($animado) :aria-live="pausado ? 'polite' : 'off'" @endif>
                @foreach ($banners->values() as $i => $banner)
                    <div class="ra-entrada-texto" role="group" aria-roledescription="diapositiva" aria-label="{{ $i + 1 }} de {{ $total }}"
                        @if ($total > 1) x-show="banner() === {{ $i }}" x-transition:enter.opacity.duration.400ms @if ($i > 0) x-cloak @endif @endif>
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
                @foreach ($diapositivas as $d => $diapositiva)
                    @continue($diapositiva['foto'] === null)
                    @php
                        ['escritorio' => $escritorio, 'movil' => $movil] = $diapositiva['foto'];
                    @endphp
                    <div class="ra-entrada-foto" @if ($animado) x-show="actual === {{ $d }}" x-transition:enter.opacity.duration.600ms @if ($d > 0) x-cloak @endif @endif>
                        <picture>
                            @if ($movil->hasGeneratedConversion('w800'))
                                <source media="(max-width: 979px)" type="image/webp" srcset="{{ $movil->getUrl('w400') }} 400w, {{ $movil->getUrl('w800') }} 800w" sizes="290px">
                            @else
                                <source media="(max-width: 979px)" srcset="{{ $movil->getUrl() }}">
                            @endif
                            @if ($escritorio->hasGeneratedConversion('w1600'))
                                <source media="(min-width: 980px)" type="image/webp" srcset="{{ $escritorio->getUrl('w1200') }} 1200w, {{ $escritorio->getUrl('w1600') }} 1600w" sizes="55vw">
                            @endif
                            <img src="{{ $escritorio->getUrl() }}" alt="{{ $diapositiva['alt'] }}" width="1600" height="900"
                                style="object-position: {{ \App\Support\Medios::foco($escritorio) }}"
                                @if ($d === 0) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                        </picture>
                        @unless ($corta)
                            <span class="ra-entrada-nombre" aria-hidden="true">Rosa María Acevedo Jaramillo</span>
                        @endunless
                    </div>
                @endforeach
                @unless ($corta)
                    @if ($conFoto)
                        <x-ra.rosa-raices class="ra-entrada-rosa" :decorativa="true" />
                    @else
                        <x-ra.rosa-raices class="ra-entrada-rosa ra-entrada-rosa-sola" :prioridad="true" />
                    @endif
                @endunless
            </div>

            @if ($animado)
                <div class="ra-entrada-puntos ra-puntos" role="group" aria-label="Controles del carrusel">
                    <button type="button" class="ra-carrusel-pausa" @click="alternar()" :aria-pressed="pausado.toString()" aria-pressed="false"
                        :aria-label="pausado ? 'Reanudar el cambio automático' : 'Pausar el cambio automático'" aria-label="Pausar el cambio automático">
                        <span class="ra-carrusel-pausa-icono" :class="pausado && 'ra-en-pausa'" aria-hidden="true"></span>
                    </button>
                    @if ($total > 1)
                        @foreach ($banners as $i => $banner)
                            <button type="button" class="ra-punto" aria-current="{{ $i === 0 ? 'true' : 'false' }}" :aria-current="(banner() === {{ $i }}).toString()" @click="ir({{ $primera[$i] }})" aria-label="Ver destacado {{ $i + 1 }}"></button>
                        @endforeach
                    @else
                        @foreach ($diapositivas as $d => $diapositiva)
                            <button type="button" class="ra-punto" aria-current="{{ $d === 0 ? 'true' : 'false' }}" :aria-current="(actual === {{ $d }}).toString()" @click="ir({{ $d }})" aria-label="Ver foto {{ $d + 1 }}"></button>
                        @endforeach
                    @endif
                </div>
            @endif

            @if ($conFormulario)
                <div class="ra-entrada-capsula">{{ $formulario }}</div>
            @endif

            <div class="ra-entrada-acciones">
                @foreach ($banners->values() as $i => $banner)
                    @if (($banner->btn1_texto && $banner->btn1_url) || ($banner->btn2_texto && $banner->btn2_url))
                        <div class="ra-acciones" @if ($total > 1) x-show="banner() === {{ $i }}" @if ($i > 0) x-cloak @endif @endif>
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
