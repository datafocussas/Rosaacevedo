@props(['columna', 'nivel' => 3])
{{-- Columna de opinión: tarjeta de texto (la voz va primero). Comillas oro, título, resumen y firma con la foto
     redonda del columnista (o sus iniciales). Toda la tarjeta es clicable y el foco se ve en la tarjeta. --}}
@php $columnista = $columna->columnista; @endphp
<article {{ $attributes->merge(['class' => 'ra-columna-tarjeta']) }} data-aparecer>
    <span class="ra-columna-comillas" aria-hidden="true">“</span>
    <h{{ $nivel }} class="ra-columna-tarjeta-titulo"><a href="{{ route('opinion.show', $columna) }}">{{ $columna->titulo }}</a></h{{ $nivel }}>
    @if ($columna->resumen)<p class="ra-texto-secundario ra-sin-margen">{{ $columna->resumen }}</p>@endif
    <div class="ra-columna-firma">
        <x-ra.avatar-columnista :columnista="$columnista" />
        <div>
            <p class="ra-columna-autor ra-sin-margen">{{ $columnista->nombre }}</p>
            <p class="ra-columna-meta ra-sin-margen">
                @if ($columna->publicada_en)<time datetime="{{ $columna->publicada_en->toDateString() }}">{{ $columna->publicada_en->translatedFormat('j M Y') }}</time><span aria-hidden="true"> · </span>@endif
                <span>{{ $columna->minutosLectura() }} min de lectura</span>
            </p>
        </div>
    </div>
</article>
