<x-layouts.sitio titulo="Noticias" descripcion="Comunicados, recorridos y columnas de la campaña de Rosa Acevedo en Itagüí.">
    <header class="ra-seccion ra-cabecera-pagina">
        <div class="ra-contenedor ra-pila-4">
            <span class="ra-etiqueta">Noticias</span>
            <h1 class="ra-display">Lo que pasa en el territorio</h1>
        </div>
    </header>
    <section class="ra-seccion-compacta">
        <div class="ra-contenedor">
            @if ($noticias->isEmpty())
                <p>Pronto publicaremos las primeras noticias.</p>
            @else
                <div class="ra-grilla ra-grilla-3">
                    @foreach ($noticias as $noticia)<x-ra.tarjeta-noticia :noticia="$noticia" :nivel="2" />@endforeach
                </div>
                @if ($noticias->hasPages())
                    <nav class="ra-paginacion" aria-label="Paginación">
                        @if ($noticias->previousPageUrl())<a class="ra-btn ra-btn-fantasma" href="{{ $noticias->previousPageUrl() }}" rel="prev">Más recientes</a>@endif
                        @if ($noticias->nextPageUrl())<a class="ra-btn ra-btn-fantasma" href="{{ $noticias->nextPageUrl() }}" rel="next">Anteriores</a>@endif
                    </nav>
                @endif
            @endif
        </div>
    </section>
    @include('partials.bloques.llamado', ['d' => []])
</x-layouts.sitio>
