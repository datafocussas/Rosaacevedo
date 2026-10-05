@php $eventos ??= \App\Models\Evento::query()->proximos()->with('comuna')->limit(3)->get(); @endphp
@if ($eventos->isNotEmpty())
<section class="ra-seccion ra-fondo-blanco" aria-labelledby="agenda-titulo">
    <div class="ra-contenedor ra-dos-columnas">
        <div class="ra-pila">
            <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Agenda' }}</span>
            <h2 class="ra-h2" id="agenda-titulo">{{ $d['titulo'] ?? 'Nos vemos en el barrio' }}</h2>
            <a class="ra-enlace-fuerte" href="{{ route('agenda') }}">Ver toda la agenda</a>
        </div>
        <x-ra.agenda :eventos="$eventos" />
    </div>
</section>
@endif
