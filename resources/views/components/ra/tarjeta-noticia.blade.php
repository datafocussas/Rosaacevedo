@props(['noticia', 'nivel' => 3, 'variante' => 'rejilla'])
{{-- Noticia (diseño v2, §6.8). «destacada»: imagen 16:10 con radio 28 y título grande; «fila»: sin imagen, para la
     lista junto a la destacada; «rejilla»: imagen 16:10 y título de 22 px. Sin imagen, un marcador abismo con la flor.
     Toda la tarjeta es clicable y el foco se ve en la tarjeta. --}}
@php
    $imagen = $variante === 'fila' ? null : $noticia->getFirstMedia('destacada');
    $clase = ['destacada' => 'ra-noticia-destacada', 'fila' => 'ra-noticia-fila', 'rejilla' => 'ra-noticia-rejilla'][$variante] ?? 'ra-noticia-rejilla';
    $claseTitulo = ['destacada' => 'ra-noticia-titulo', 'fila' => 'ra-noticia-fila-titulo', 'rejilla' => 'ra-noticia-rejilla-titulo'][$variante] ?? 'ra-noticia-rejilla-titulo';
@endphp
<article {{ $attributes->merge(['class' => $clase]) }} data-aparecer>
    @if ($variante !== 'fila')
        <div class="ra-noticia-imagen">
            @if ($imagen)
                <img src="{{ \App\Support\Medios::url($imagen, $variante === 'destacada' ? 'w1200' : 'w800') }}" alt="" loading="lazy" decoding="async" width="800" height="500" style="object-position: {{ \App\Support\Medios::foco($imagen, [50, 30]) }}">
            @else
                <x-ra.rosa-flor />
            @endif
        </div>
    @endif
    <div class="ra-pila">
        <p class="ra-noticia-meta ra-sin-margen">
            @if ($noticia->eje)<span class="ra-noticia-cat">{{ $noticia->eje->nombreCorto() }}</span><span aria-hidden="true">·</span>@endif
            @if ($noticia->publicada_en)<time datetime="{{ $noticia->publicada_en->toDateString() }}">{{ $noticia->publicada_en->translatedFormat('j M Y') }}</time>@endif
        </p>
        <h{{ $nivel }} class="{{ $claseTitulo }}"><a href="{{ route('noticias.show', $noticia) }}">{{ $noticia->titulo }}</a></h{{ $nivel }}>
        @if ($variante === 'destacada' && $noticia->resumen)<p class="ra-texto-secundario ra-sin-margen">{{ $noticia->resumen }}</p>@endif
    </div>
</article>
