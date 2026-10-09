{{-- Propuestas: rejilla bento (diseño v2, §6.4). Sin ejes publicados no se pinta. --}}
@php
    $ejes ??= \App\Models\Eje::query()->publicados()->get();
    $titulo = $d['titulo'] ?? 'Aquí me planto por…';
    // La última palabra del título va en coral hondo («por…»).
    $partes = preg_split('/\s+(?=\S+$)/u', trim($titulo));
    $texto = $d['texto'] ?? 'No prometemos veinte obras. Nos comprometemos con un propósito: que ningún joven tenga que irse de Itagüí para cumplir sus sueños.';
@endphp
@if ($ejes->isNotEmpty())
<section class="ra-seccion {{ $clasesFondo }}" aria-labelledby="ejes-titulo-{{ $loop->index ?? 0 }}">
    <div class="ra-contenedor">
        <div class="ra-encabezado-seccion">
            <div class="ra-pila-4">
                <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Propuestas' }}</span>
                <h2 class="ra-h2" id="ejes-titulo-{{ $loop->index ?? 0 }}">@if (count($partes) === 2){{ $partes[0] }} <span class="ra-acento">{{ $partes[1] }}</span>@else{{ $titulo }}@endif</h2>
            </div>
            @if (filled($texto))<p class="ra-entradilla">{{ $texto }}</p>@endif
        </div>
        <x-ra.ejes :ejes="$ejes" />
    </div>
</section>
@endif
