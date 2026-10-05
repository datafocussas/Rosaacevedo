@if (! empty($d['imagen']))
<figure class="ra-seccion-compacta ra-contenedor ra-figura">
    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($d['imagen']) }}" alt="{{ $d['alt'] ?? '' }}" loading="lazy" decoding="async">
    @if (! empty($d['pie']))<figcaption class="ra-pequeno">{{ $d['pie'] }}</figcaption>@endif
</figure>
@endif
