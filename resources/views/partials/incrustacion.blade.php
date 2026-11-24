{{-- Video o publicación de una red dentro del texto (App\Support\Incrustacion). Videos: portada (miniatura de YouTube)
     y el reproductor se crea al hacer clic. Redes: aviso con botón; el marco de la red se carga si la persona acepta
     las cookies de redes sociales o hace clic en «Mostrar la publicación aquí». Sin JavaScript queda el enlace. --}}
@php
    $nombre = $datos['nombre'];
    $titulo = $datos['titulo'] ?? 'Publicación de '.$nombre;
    $icono = in_array($datos['red'], ['youtube', 'instagram', 'tiktok', 'x', 'facebook'], true) ? $datos['red'] : 'play';
@endphp
@if ($datos['tipo'] === 'video')
    <figure class="ra-incrustacion ra-incrustacion-video" x-data="{ cargar: false }">
        <div class="ra-video{{ $datos['vertical'] ? ' ra-video-vertical' : '' }}">
            <template x-if="cargar">
                <iframe src="{{ $datos['src'] }}&autoplay=1" title="Video de {{ $nombre }}" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
            </template>
            <button type="button" class="ra-video-portada" x-show="!cargar" @click="cargar = true" aria-label="Reproducir el video de {{ $nombre }}">
                @if ($datos['miniatura'])
                    <img src="{{ $datos['miniatura'] }}" alt="" loading="lazy" decoding="async">
                @endif
                <span class="ra-video-boton"><x-ra.icono nombre="play" /></span>
            </button>
        </div>
        <figcaption class="ra-pequeno"><a href="{{ $datos['url'] }}" target="_blank" rel="noopener">Ver en {{ $nombre }}</a>. Al reproducirlo te conectas con {{ $nombre }}.</figcaption>
    </figure>
@else
    <figure class="ra-incrustacion ra-incrustacion-red ra-incrustacion-{{ $datos['red'] }}" x-data="incrustacion()" style="--incrustacion-ancho: {{ $datos['ancho'] }}px">
        <template x-if="cargar">
            <iframe src="{{ $datos['src'] }}" title="{{ $titulo }}" style="height: {{ $datos['alto'] }}px" loading="lazy"
                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowfullscreen></iframe>
        </template>
        <div class="ra-incrustacion-aviso" x-show="!cargar">
            <span class="ra-incrustacion-icono"><x-ra.icono :nombre="$icono" /></span>
            <p class="ra-incrustacion-titulo">{{ $titulo }}</p>
            <button type="button" class="ra-btn ra-btn-secundario" @click="cargar = true" x-cloak>{{ isset($datos['titulo']) ? 'Mostrar el perfil aquí' : 'Mostrar la publicación aquí' }}</button>
            <p class="ra-pequeno ra-sin-margen">
                <a href="{{ $datos['url'] }}" target="_blank" rel="noopener">Verla en {{ $nombre }}</a>.
                <span x-cloak>Al mostrarla aquí, {{ $nombre }} puede usar cookies. Para verlas siempre, acepta las de redes sociales en las <button type="button" class="ra-enlace-boton" data-abrir-cookies>preferencias de cookies</button>.</span>
            </p>
        </div>
        <figcaption class="ra-pequeno" x-show="cargar" x-cloak><a href="{{ $datos['url'] }}" target="_blank" rel="noopener">Ver en {{ $nombre }}</a></figcaption>
    </figure>
@endif
