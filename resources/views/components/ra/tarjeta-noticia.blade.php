@props(['noticia', 'nivel' => 3])
@php $imagen = $noticia->getFirstMedia('destacada'); @endphp
<article class="ra-tarjeta">
    @if ($imagen)
        <img src="{{ $imagen->hasGeneratedConversion('w800') ? $imagen->getUrl('w800') : $imagen->getUrl() }}" alt="" loading="lazy" decoding="async" width="800" height="450">
    @endif
    <div class="ra-tarjeta-cuerpo">
        @if ($noticia->eje)<span class="ra-chip ra-chip-raiz">{{ $noticia->eje->nombreCorto() }}</span>@endif
        <h{{ $nivel }} class="ra-h3 ra-tarjeta-titulo"><a href="{{ route('noticias.show', $noticia) }}">{{ $noticia->titulo }}</a></h{{ $nivel }}>
        @if ($noticia->publicada_en)
            <time class="ra-pequeno" datetime="{{ $noticia->publicada_en->toDateString() }}">{{ $noticia->publicada_en->translatedFormat('j \d\e F \d\e Y') }}</time>
        @endif
    </div>
</article>
