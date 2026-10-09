@props(['comunas', 'actual' => null, 'raices' => true])
{{-- Tu comuna (diseño v2, §6.6): red de raíces decorativa y 8 tarjetas (7 comunas + El Manzanillo, Acuerdo 017 de 2024).
     Una comuna sin página publicada se muestra sin enlace, en tinta suave, con la etiqueta «Pronto». --}}
@if ($raices)
    <svg class="ra-red-raices" viewBox="0 0 1176 90" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <defs><linearGradient id="red-raices-trazo" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#c9a86a"/><stop offset="1" stop-color="#0f5c45"/></linearGradient></defs>
        <g fill="none" stroke="url(#red-raices-trazo)" stroke-width="2" vector-effect="non-scaling-stroke">
            @foreach ([73, 220, 367, 514, 661, 808, 955, 1102] as $x)
                <path d="M588 6 C588 46 {{ $x }} 40 {{ $x }} 88" />
            @endforeach
        </g>
        <circle cx="588" cy="6" r="5" fill="#c9a86a" />
    </svg>
@endif
<div class="ra-comunas">
    @foreach ($comunas as $comuna)
        @php
            $corregimiento = $comuna->esCorregimiento();
            $publicada = (bool) $comuna->pagina?->publicada;
            $numero = $corregimiento ? 'El M.' : str_pad(ltrim(substr($comuna->codigo, 1), '0'), 2, '0', STR_PAD_LEFT);
            $clases = 'ra-comuna'.($corregimiento ? ' ra-comuna-corregimiento' : '').($publicada ? '' : ' ra-comuna-pronto');
        @endphp
        @if ($publicada)
            <a class="{{ $clases }}" href="{{ route('comunas.show', $comuna) }}" @if ($actual && $actual->is($comuna)) aria-current="true" @endif>
        @else
            <div class="{{ $clases }}">
        @endif
            <span class="ra-comuna-num" aria-hidden="true">{{ $numero }}</span>
            <span class="ra-comuna-nombre">{{ $comuna->nombreCorto() }}</span>
            <span class="ra-comuna-barrios">{{ $comuna->rotulo() }}</span>
            @unless ($publicada)<span class="ra-pronto">Pronto</span>@endunless
        @if ($publicada)
            </a>
        @else
            </div>
        @endif
    @endforeach
</div>
