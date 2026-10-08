{{-- Aviso de escucha ciudadana (ajuste «aviso_global»): franja verde bajo la entrada. El verde es el color
     que la ciudadanía asocia con el bienestar; se usa en todo lo que es escucha y comunidad. --}}
@php
    $aviso = app(\App\Services\Ajustes::class)->get('aviso_global');
@endphp
@if (is_array($aviso) && ($aviso['activo'] ?? false) && filled($aviso['texto'] ?? null))
    @php
        // «Escucha ciudadana abierta: deja tu propuesta…» → rótulo + frase.
        [$rotulo, $frase] = str_contains($aviso['texto'], ':') ? array_map('trim', explode(':', $aviso['texto'], 2)) : [null, $aviso['texto']];
        $frase = mb_strtoupper(mb_substr($frase, 0, 1)).mb_substr($frase, 1);
    @endphp
    <section class="ra-escucha" aria-label="{{ $rotulo ?? 'Escucha ciudadana' }}">
        <div class="ra-contenedor ra-escucha-fila">
            <div class="ra-escucha-texto">
                <span class="ra-escucha-icono" aria-hidden="true"><x-ra.icono nombre="sprout" /></span>
                <div>
                    @if ($rotulo)<span class="ra-etiqueta">{{ $rotulo }}</span>@endif
                    <p class="ra-escucha-frase">{{ $frase }}</p>
                </div>
            </div>
            @if (filled($aviso['url'] ?? null))
                <a class="ra-btn ra-btn-claro" href="{{ $aviso['url'] }}">{{ $aviso['boton'] ?? 'Deja tu propuesta' }} <x-ra.icono nombre="arrow-right" /></a>
            @endif
        </div>
    </section>
@endif
