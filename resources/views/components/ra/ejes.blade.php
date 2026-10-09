@props(['ejes', 'variante' => 'bento'])
{{-- Ejes «Aquí me planto por…» (diseño v2, §6.4).
     «bento»: el eje de orden 1 es la tarjeta grande (2 × 2 en escritorio; ancho completo en móvil); los demás, tarjetas
     blancas; la última celda es la tarjeta coral «Todas las propuestas», que se estira para completar la fila.
     Los numerales salen del orden de publicación y son decorativos. «lista»: filas con el ícono en círculo coral. --}}
@php $ejes = $ejes->values(); @endphp
@if ($ejes->isNotEmpty())
    @if ($variante === 'lista')
        <nav class="ra-ejes-lista" aria-label="Ejes de propuesta" {{ $attributes }}>
            @foreach ($ejes as $eje)
                <a class="ra-eje" href="{{ route('propuestas.eje', $eje) }}">
                    <span class="ra-eje-icono"><x-ra.icono :nombre="$eje->icono" /></span>
                    <span class="ra-eje-texto"><small>Por {{ $eje->articulo }}</small> <strong>{{ $eje->sujeto }}</strong></span>
                </a>
            @endforeach
        </nav>
    @else
        @php
            $pequenos = $ejes->count() - 1;
            // Escritorio (4 columnas): la grande ocupa 2 × 2 y deja 4 celdas a su derecha.
            if ($pequenos < 4) {
                $libres = 4 - $pequenos;
                $spanEscritorio = $libres >= 2 ? 2 : 1;
                $altaEscritorio = $libres === 4;
            } else {
                $ultimaFila = ($pequenos - 4) % 4;
                $spanEscritorio = 4 - $ultimaFila;
                $altaEscritorio = false;
            }
            // Móvil (2 columnas): la grande va a lo ancho.
            $spanMovil = $pequenos % 2 === 0 ? 2 : 1;
        @endphp
        <nav class="ra-bento" aria-label="Ejes de propuesta" {{ $attributes }}>
            @foreach ($ejes as $i => $eje)
                <a class="ra-bento-celda {{ $i === 0 ? 'ra-bento-grande' : '' }}" href="{{ route('propuestas.eje', $eje) }}" data-aparecer>
                    <span class="ra-bento-num" aria-hidden="true">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <span>
                        <span class="ra-bento-articulo">Por {{ $eje->articulo }}</span>
                        <span class="ra-bento-titulo">{{ \Illuminate\Support\Str::ucfirst($eje->sujeto) }}</span>
                        @if ($i === 0 && $eje->frase)<span class="ra-bento-frase" >{{ $eje->frase }}</span>@endif
                    </span>
                </a>
            @endforeach
            <a class="ra-bento-celda ra-bento-coral ra-bento-m-span-{{ $spanMovil }} ra-bento-d-span-{{ $spanEscritorio }} {{ $altaEscritorio ? 'ra-bento-d-alta' : '' }}" href="{{ route('propuestas') }}" data-aparecer>
                <span class="ra-bento-articulo">Todas las propuestas</span>
                <span class="ra-bento-titulo">Conócelas y propón la tuya <span aria-hidden="true">→</span></span>
            </a>
        </nav>
    @endif
@endif
