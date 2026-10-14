@props(['noticias', 'nivel' => 3])
{{-- Bloque de noticias (diseño v2, §6.8): la más reciente destacada y a su derecha la lista con las siguientes.
     0: no se pinta · 1: solo la destacada a lo ancho · 2–4: destacada + lista de 1 a 3. --}}
@php $noticias = $noticias->take(4)->values(); @endphp
@if ($noticias->isNotEmpty())
    <div class="ra-noticias {{ $noticias->count() > 1 ? 'ra-noticias-con-lista' : '' }}">
        <x-ra.tarjeta-noticia :noticia="$noticias->first()" variante="destacada" :nivel="$nivel" :class="$noticias->count() === 1 ? 'ra-noticia-sola' : ''" />
        @if ($noticias->count() > 1)
            <ul class="ra-noticias-lista">
                @foreach ($noticias->slice(1) as $noticia)
                    <li><x-ra.tarjeta-noticia :noticia="$noticia" variante="fila" :nivel="$nivel" /></li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
