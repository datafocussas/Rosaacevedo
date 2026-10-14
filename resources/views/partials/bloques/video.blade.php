{{-- Video (diseño v2, §7): noche; portada con botón circular coral; el iframe se crea al hacer clic. --}}
@php $incrustar = \App\Support\Video::incrustar($d['url'] ?? null); @endphp
@if ($incrustar)
<section class="ra-seccion {{ $clasesFondo ?? 'ra-fondo-noche ra-oscuro' }}">
    <div class="ra-contenedor">
        <div class="ra-lectura-centrada ra-pila-4" x-data="{ cargar: false }">
            @if (! empty($d['titulo']))<h2 class="ra-h2-medio">{{ $d['titulo'] }}</h2>@endif
            <div class="ra-video">
                <template x-if="cargar">
                    <iframe src="{{ $incrustar }}&autoplay=1" title="{{ $d['titulo'] ?? 'Video' }}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                </template>
                <button type="button" class="ra-video-portada" x-show="!cargar" @click="cargar = true" aria-label="Reproducir el video{{ ! empty($d['titulo']) ? ': '.$d['titulo'] : '' }}">
                    <span class="ra-video-boton"><x-ra.icono nombre="play" /></span>
                </button>
            </div>
            <p class="ra-pequeno ra-sin-margen"><a href="{{ $d['url'] }}" target="_blank" rel="noopener">Ver en la plataforma original</a>. Al reproducirlo te conectas con un servicio externo.</p>
        </div>
    </div>
</section>
@endif
