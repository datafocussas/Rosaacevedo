<x-layouts.sitio titulo="Noticias" descripcion="Comunicados, recorridos y columnas de la campaña de Rosa Acevedo en Itagüí.">
    <x-ra.cabecera etiqueta="Noticias" titulo="Lo que pasa en el territorio" />
    <section class="ra-seccion ra-fondo-marfil">
        <div class="ra-contenedor ra-pila-5">
            @if ($noticias->isEmpty())
                <p class="ra-entradilla">Pronto publicaremos las primeras noticias.</p>
            @else
                @if ($noticias->onFirstPage())
                    <x-ra.tarjeta-noticia :noticia="$noticias->first()" variante="destacada" :nivel="2" class="ra-noticia-sola" />
                    <div class="ra-noticias-rejilla">
                        @foreach ($noticias->slice(1) as $noticia)<x-ra.tarjeta-noticia :noticia="$noticia" :nivel="2" />@endforeach
                    </div>
                @else
                    <div class="ra-noticias-rejilla">
                        @foreach ($noticias as $noticia)<x-ra.tarjeta-noticia :noticia="$noticia" :nivel="2" />@endforeach
                    </div>
                @endif
                @if ($noticias->hasPages())
                    <nav class="ra-paginacion" aria-label="Paginación">
                        @if ($noticias->previousPageUrl())<a class="ra-btn ra-btn-fantasma" href="{{ $noticias->previousPageUrl() }}" rel="prev">Más recientes</a>@endif
                        @if ($noticias->nextPageUrl())<a class="ra-btn ra-btn-fantasma" href="{{ $noticias->nextPageUrl() }}" rel="next">Anteriores</a>@endif
                    </nav>
                @endif
            @endif
        </div>
    </section>
    @include('partials.bloques.llamado', ['d' => [], 'clasesFondo' => 'ra-fondo-esmeralda ra-oscuro'])
</x-layouts.sitio>
