{{-- Franja «Raíces» (fondo verde-tenue). Ningún dato biográfico sin confirmar: se edita en el panel. --}}
<section class="ra-seccion ra-franja-raiz" aria-labelledby="raices-titulo">
    <div class="ra-contenedor ra-dos-columnas ra-centrado">
        @if (! empty($d['imagen']))
            <img class="ra-foto-cuadrada" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($d['imagen']) }}" alt="{{ $d['alt'] ?? 'Rosa Acevedo' }}" loading="lazy" decoding="async" width="600" height="600">
        @else
            <x-ra.rosa-raices class="ra-rosa-grande" />
        @endif
        <div class="ra-pila-4">
            <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Raíces' }}</span>
            <h2 class="ra-h2" id="raices-titulo">{{ $d['titulo'] ?? 'Rosa no se trasplanta' }}</h2>
            <p class="ra-cuerpo-lg ra-sin-margen">{!! \App\Support\Texto::marcarPendientes(e($d['texto'] ?? '[POR CONFIRMAR] Trayectoria de Rosa al servicio de Itagüí, con los cargos y periodos confirmados por la campaña.')) !!}</p>
            <a class="ra-enlace-fuerte" href="{{ $d['enlace_url'] ?? route('manifiesto') }}">{{ $d['enlace_texto'] ?? 'Lee el manifiesto' }}</a>
        </div>
    </div>
</section>
