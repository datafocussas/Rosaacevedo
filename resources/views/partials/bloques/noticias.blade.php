@php $noticias ??= \App\Models\Noticia::query()->publicadas()->with(['eje', 'media'])->limit(3)->get(); @endphp
@if ($noticias->isNotEmpty())
<section class="ra-seccion" aria-labelledby="noticias-titulo">
    <div class="ra-contenedor">
        <div class="ra-fila-titulo">
            <h2 class="ra-h2" id="noticias-titulo">{{ $d['titulo'] ?? 'Noticias' }}</h2>
            <a class="ra-enlace-fuerte" href="{{ route('noticias') }}">Ver todas<span class="ra-sr"> las noticias</span></a>
        </div>
        <div class="ra-grilla ra-grilla-3">
            @foreach ($noticias as $noticia)
                <x-ra.tarjeta-noticia :noticia="$noticia" />
            @endforeach
        </div>
    </div>
</section>
@endif
