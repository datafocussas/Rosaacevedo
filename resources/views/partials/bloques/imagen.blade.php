@if (! empty($d['imagen']))
<section class="ra-seccion-compacta {{ $clasesFondo }}">
    <figure class="ra-contenedor ra-figura" data-aparecer>
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($d['imagen']) }}" alt="{{ $d['alt'] ?? '' }}" loading="lazy" decoding="async">
        @if (! empty($d['pie']))<figcaption>{{ $d['pie'] }}</figcaption>@endif
    </figure>
</section>
@endif
