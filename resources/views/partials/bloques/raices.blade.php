{{-- Raíces (diseño v2, §6.5): abismo; cita (el título del bloque, entre comillas) con texto y enlace; a la derecha hasta
     3 cifras con filo oro, o la imagen con tratamiento cinematográfico si el bloque la trae.
     Ningún dato biográfico ni cifra sin confirmar con su fuente: si falta, [POR CONFIRMAR]. --}}
@php
    $cifras = collect($d['cifras'] ?? [])->filter(fn ($c) => filled($c['valor'] ?? null))->take(3);
    $cita = trim($d['titulo'] ?? 'Mis raíces están aquí. Y las raíces no se trasplantan.', " \t\n\"“”");
@endphp
<section class="ra-seccion {{ $clasesFondo }}" aria-labelledby="raices-titulo">
    <div class="ra-contenedor ra-raices-rejilla">
        <div class="ra-pila-5" data-aparecer>
            <span class="ra-etiqueta ra-etiqueta-jade">{{ $d['etiqueta'] ?? 'Raíces' }}</span>
            <h2 class="ra-cita-grande" id="raices-titulo">“{{ $cita }}”</h2>
            @if (filled($d['texto'] ?? null))
                <p class="ra-entradilla">{!! \App\Support\Texto::marcarPendientes(e($d['texto'])) !!}</p>
            @endif
            <a class="ra-enlace-fuerte" href="{{ $d['enlace_url'] ?? route('manifiesto') }}">{{ $d['enlace_texto'] ?? 'Lee el manifiesto' }}</a>
        </div>
        @if (! empty($d['imagen']))
            <div class="ra-foto-cine" data-aparecer>
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($d['imagen']) }}" alt="{{ $d['alt'] ?? 'Rosa Acevedo' }}" loading="lazy" decoding="async">
            </div>
        @elseif ($cifras->isNotEmpty())
            <div class="ra-cifras ra-cifras-{{ max(2, $cifras->count()) }}">
                @foreach ($cifras as $cifra)
                    <div class="ra-cifra" data-aparecer>
                        <span class="ra-cifra-valor">{!! \App\Support\Texto::marcarPendientes(e($cifra['valor'])) !!}</span>
                        <span class="ra-cifra-texto">{!! \App\Support\Texto::marcarPendientes(e($cifra['texto'] ?? '')) !!}</span>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Pieza «Raíces» de la campaña: brote verde con raíces luminosas (fuente: public/img/rosa-raices.png), ya fundida
                 sobre el color abismo, que es el único fondo permitido de este bloque. --}}
            <figure class="ra-raices-imagen" data-aparecer>
                <img src="{{ asset('img/marca/raices-520.webp') }}" srcset="{{ asset('img/marca/raices-520.webp') }} 520w, {{ asset('img/marca/raices-900.webp') }} 900w"
                    sizes="(min-width: 980px) 480px, 90vw" width="900" height="940" alt="" loading="lazy" decoding="async">
            </figure>
        @endif
    </div>
</section>
