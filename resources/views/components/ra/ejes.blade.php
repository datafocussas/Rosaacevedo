@props(['ejes'])
{{-- «Aquí me planto por…» con íconos circulares (EjesPropuesta). --}}
<nav aria-label="Ejes de propuesta" {{ $attributes }}>
    @foreach ($ejes as $eje)
        <a class="ra-eje" href="{{ route('propuestas.eje', $eje) }}">
            <span class="ra-eje-icono"><x-ra.icono :nombre="$eje->icono" /></span>
            <span class="ra-eje-texto">Por {{ $eje->articulo }} <strong>{{ $eje->sujeto }}</strong></span>
        </a>
    @endforeach
</nav>
