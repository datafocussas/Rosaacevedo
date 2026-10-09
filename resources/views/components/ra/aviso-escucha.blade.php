{{-- Aviso de escucha ciudadana (ajuste «aviso_global»): franja esmeralda compacta bajo la entrada (diseño v2, §6.11).
     El texto se separa por «:» en rótulo y frase. Inactivo: no se pinta. --}}
@php $aviso = app(\App\Services\Ajustes::class)->get('aviso_global'); @endphp
@if (is_array($aviso) && ($aviso['activo'] ?? false) && filled($aviso['texto'] ?? null))
    @php
        [$rotulo, $frase] = str_contains($aviso['texto'], ':') ? array_map('trim', explode(':', $aviso['texto'], 2)) : [null, $aviso['texto']];
        $frase = mb_strtoupper(mb_substr($frase, 0, 1)).mb_substr($frase, 1);
    @endphp
    <section class="ra-escucha ra-fondo-esmeralda ra-oscuro" aria-label="{{ $rotulo ?? 'Escucha ciudadana' }}">
        <div class="ra-contenedor ra-escucha-fila">
            <div class="ra-escucha-texto">
                @if ($rotulo)<span class="ra-etiqueta">{{ $rotulo }}</span>@endif
                <p class="ra-escucha-frase">{{ $frase }}</p>
            </div>
            @if (filled($aviso['url'] ?? null))
                <x-ra.boton :href="$aviso['url']" variante="fantasma" icono="arrow-right" class="ra-btn-compacto">{{ $aviso['boton'] ?? 'Deja tu propuesta' }}</x-ra.boton>
            @endif
        </div>
    </section>
@endif
