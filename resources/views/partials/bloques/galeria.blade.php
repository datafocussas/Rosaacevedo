@php $imagenes = $d['imagenes'] ?? []; @endphp
@if ($imagenes)
<section class="ra-seccion {{ $clasesFondo }}">
    <div class="ra-contenedor ra-pila-5">
        @if (! empty($d['titulo']))<h2 class="ra-h2-medio">{{ $d['titulo'] }}</h2>@endif
        <div class="ra-galeria {{ count($imagenes) >= 5 ? 'ra-galeria-con-grande' : '' }}">
            @foreach ($imagenes as $imagen)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($imagen) }}" alt="{{ $d['alt'] ?? '' }}" loading="lazy" decoding="async" data-aparecer>
            @endforeach
        </div>
    </div>
</section>
@endif
