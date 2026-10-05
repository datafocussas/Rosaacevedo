@php $imagenes = $d['imagenes'] ?? []; @endphp
@if ($imagenes)
<section class="ra-seccion-compacta">
    <div class="ra-contenedor">
        @if (! empty($d['titulo']))<h2 class="ra-h2 ra-titulo-seccion">{{ $d['titulo'] }}</h2>@endif
        <div class="ra-grilla ra-grilla-3">
            @foreach ($imagenes as $imagen)
                <img class="ra-galeria-foto" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($imagen) }}" alt="{{ $d['alt'] ?? '' }}" loading="lazy" decoding="async">
            @endforeach
        </div>
    </div>
</section>
@endif
