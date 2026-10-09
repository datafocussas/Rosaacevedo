@props(['redes', 'variante' => 'pildoras'])
{{-- Redes activas en el orden del panel. «pildoras»: nombre + ícono (franja de redes); «iconos»: solo círculos. --}}
<div {{ $attributes->merge(['class' => $variante === 'iconos' ? 'ra-redes-iconos' : 'ra-redes']) }}>
    @foreach ($redes as $red)
        @if ($variante === 'iconos')
            <a class="ra-red" href="{{ $red->url }}" target="_blank" rel="noopener" aria-label="Rosa Acevedo en {{ $red->nombre }} (se abre en una pestaña nueva)"><x-ra.icono :nombre="$red->icono" /></a>
        @else
            <a class="ra-red-pildora" href="{{ $red->url }}" target="_blank" rel="noopener"><x-ra.icono :nombre="$red->icono" /> {{ $red->nombre }}<span class="ra-sr"> (se abre en una pestaña nueva)</span></a>
        @endif
    @endforeach
    {{ $slot }}
</div>
