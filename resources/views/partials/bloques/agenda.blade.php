{{-- Agenda (diseño v2, §6.9): arena, tarjetas en rejilla de 3. Sin eventos futuros no se pinta. --}}
@php $eventos ??= \App\Models\Evento::query()->proximos()->with('comuna')->limit(3)->get(); @endphp
@if ($eventos->isNotEmpty())
<section class="ra-seccion {{ $clasesFondo }}" aria-labelledby="agenda-titulo">
    <div class="ra-contenedor">
        <div class="ra-fila-titulo">
            <div class="ra-pila">
                <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Agenda' }}</span>
                <h2 class="ra-h2" id="agenda-titulo">{{ $d['titulo'] ?? 'Nos vemos en el barrio' }}</h2>
            </div>
            <a class="ra-enlace-fuerte" href="{{ route('agenda') }}">Ver toda la agenda</a>
        </div>
        <x-ra.agenda :eventos="$eventos" />
    </div>
</section>
@endif
