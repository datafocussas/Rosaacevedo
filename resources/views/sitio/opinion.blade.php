<x-layouts.sitio titulo="Opinión" descripcion="Columnas de opinión sobre Itagüí: voces de la comunidad y del equipo de Rosa Acevedo.">
    <x-ra.cabecera etiqueta="Opinión" titulo="Voces que siembran" entradilla="Columnas sobre Itagüí, sus barrios y su futuro." />
    <section class="ra-seccion ra-fondo-marfil">
        <div class="ra-contenedor ra-pila-5">
            @if ($columnas->isEmpty())
                <p class="ra-entradilla">Pronto publicaremos las primeras columnas.</p>
            @else
                <div class="ra-columnas-rejilla">
                    @foreach ($columnas as $columna)<x-ra.tarjeta-columna :columna="$columna" :nivel="2" />@endforeach
                </div>
                @if ($columnas->hasPages())
                    <nav class="ra-paginacion" aria-label="Paginación">
                        @if ($columnas->previousPageUrl())<a class="ra-btn ra-btn-fantasma" href="{{ $columnas->previousPageUrl() }}" rel="prev">Más recientes</a>@endif
                        @if ($columnas->nextPageUrl())<a class="ra-btn ra-btn-fantasma" href="{{ $columnas->nextPageUrl() }}" rel="next">Anteriores</a>@endif
                    </nav>
                @endif
            @endif
            <p class="ra-columna-aviso">Las columnas de opinión expresan el pensamiento de quienes las firman.</p>
        </div>
    </section>
    @include('partials.bloques.llamado', ['d' => [], 'clasesFondo' => 'ra-fondo-esmeralda ra-oscuro'])
</x-layouts.sitio>
