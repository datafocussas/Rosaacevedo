<section class="ra-seccion {{ ($d['fondo'] ?? '') === 'raiz' ? 'ra-franja-raiz' : '' }}">
    <div class="ra-contenedor ra-dos-columnas ra-centrado {{ ! empty($d['invertir']) ? 'ra-invertido' : '' }}">
        @if (! empty($d['imagen']))
            <img class="ra-foto-cuadrada" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($d['imagen']) }}" alt="{{ $d['alt'] ?? '' }}" loading="lazy" decoding="async">
        @endif
        <div class="ra-pila-4">
            @if (! empty($d['etiqueta']))<span class="ra-etiqueta">{{ $d['etiqueta'] }}</span>@endif
            @if (! empty($d['titulo']))<h2 class="ra-h2">{{ $d['titulo'] }}</h2>@endif
            <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($d['texto'] ?? '') }}</div>
        </div>
    </div>
</section>
