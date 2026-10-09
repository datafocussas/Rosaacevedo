{{-- Tu comuna (diseño v2, §6.6). --}}
@php $comunas ??= \App\Http\Controllers\SitioController::comunasConConteo(); @endphp
<section class="ra-seccion {{ $clasesFondo }}" aria-labelledby="comunas-titulo">
    <div class="ra-contenedor">
        <div class="ra-encabezado-seccion-centrado">
            <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Tu comuna' }}</span>
            <h2 class="ra-h2" id="comunas-titulo">{{ $d['titulo'] ?? 'Una raíz en cada territorio' }}</h2>
            <p class="ra-entradilla">{{ $d['texto'] ?? 'Siete comunas y el corregimiento El Manzanillo. Elige el tuyo y conoce lo que proponemos allí.' }}</p>
        </div>
        <x-ra.selector-comuna :comunas="$comunas" />
    </div>
</section>
