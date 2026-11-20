{{-- Últimas columnas de opinión (3). Sin columnas publicadas no se pinta. --}}
@php $columnas = \App\Models\Columna::query()->publicadas()->with(['columnista.media'])->limit(3)->get(); @endphp
@if ($columnas->isNotEmpty())
<section class="ra-seccion {{ $clasesFondo }}" aria-labelledby="opinion-titulo">
    <div class="ra-contenedor ra-pila-5">
        <div class="ra-fila-titulo">
            <h2 class="ra-h2" id="opinion-titulo">{{ $d['titulo'] ?? 'Opinión' }}</h2>
            <a class="ra-enlace-fuerte" href="{{ route('opinion') }}">Ver todas<span class="ra-sr"> las columnas</span></a>
        </div>
        <div class="ra-columnas-rejilla">
            @foreach ($columnas as $columna)<x-ra.tarjeta-columna :columna="$columna" />@endforeach
        </div>
    </div>
</section>
@endif
