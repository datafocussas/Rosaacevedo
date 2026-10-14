{{-- Noticias (diseño v2, §6.8): destacada + lista. Sin noticias no se pinta. --}}
@php $noticias ??= \App\Models\Noticia::query()->publicadas()->with(['eje', 'media'])->limit(4)->get(); @endphp
@if ($noticias->isNotEmpty())
<section class="ra-seccion {{ $clasesFondo }}" aria-labelledby="noticias-titulo">
    <div class="ra-contenedor">
        <div class="ra-fila-titulo">
            <h2 class="ra-h2" id="noticias-titulo">{{ $d['titulo'] ?? 'Noticias' }}</h2>
            <a class="ra-enlace-fuerte" href="{{ route('noticias') }}">Ver todas<span class="ra-sr"> las noticias</span></a>
        </div>
        <x-ra.noticias :noticias="$noticias" />
    </div>
</section>
@endif
