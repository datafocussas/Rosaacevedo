<section class="ra-seccion {{ $clasesFondo }}">
    <div class="ra-contenedor ra-imagen-texto {{ ! empty($d['invertir']) ? 'ra-invertido' : '' }}">
        @if (! empty($d['imagen']))
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($d['imagen']) }}" alt="{{ $d['alt'] ?? '' }}" loading="lazy" decoding="async" data-aparecer>
        @endif
        <div class="ra-pila-4" data-aparecer>
            @if (! empty($d['etiqueta']))<span class="ra-etiqueta">{{ $d['etiqueta'] }}</span>@endif
            @if (! empty($d['titulo']))<h2 class="ra-h2-medio">{{ $d['titulo'] }}</h2>@endif
            <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($d['texto'] ?? '') }}</div>
        </div>
    </div>
</section>
