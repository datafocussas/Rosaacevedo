{{-- Video con carga al hacer clic: no se conecta con YouTube/Vimeo hasta que la persona lo pide. --}}
@php $incrustar = \App\Support\Video::incrustar($d['url'] ?? null); @endphp
@if ($incrustar)
<section class="ra-seccion-compacta">
    <div class="ra-contenedor ra-lectura" x-data="{ cargar: false }">
        @if (! empty($d['titulo']))<h2 class="ra-h3 ra-titulo-seccion">{{ $d['titulo'] }}</h2>@endif
        <div class="ra-video">
            <template x-if="cargar">
                <iframe src="{{ $incrustar }}&autoplay=1" title="{{ $d['titulo'] ?? 'Video' }}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen loading="lazy"></iframe>
            </template>
            <button type="button" class="ra-video-portada" x-show="!cargar" @click="cargar = true">
                <span class="ra-btn ra-btn-principal">Ver el video</span>
            </button>
        </div>
        <p class="ra-pequeno"><a href="{{ $d['url'] }}" target="_blank" rel="noopener">Ver en la plataforma original</a>. Al reproducirlo te conectas con un servicio externo.</p>
    </div>
</section>
@endif
