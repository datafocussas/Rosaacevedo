@props(['redes', 'utm' => null])
<div {{ $attributes->merge(['class' => 'ra-redes']) }}>
    @foreach ($redes as $red)
        <a class="ra-red" href="{{ $red->url }}" target="_blank" rel="noopener" aria-label="Rosa Acevedo en {{ $red->nombre }} (se abre en una pestaña nueva)"><x-ra.icono :nombre="$red->icono" /></a>
    @endforeach
</div>
