@props(['comunas', 'actual' => null, 'raices' => true])
{{-- Tu comuna (diseño v2, §6.6): raíces decorativas y 8 tarjetas (7 comunas + El Manzanillo, Acuerdo 017 de 2024).
     Las comunas van solo con su número («Comuna 4»); el corregimiento con su nombre.
     Una comuna sin página publicada se muestra sin enlace, en tinta suave, con la etiqueta «Pronto». --}}
@if ($raices)
    {{-- Las raíces de la pieza de la campaña (brote con raíces), en tinta esmeralda para fondo claro, para que el sitio
         use una sola ilustración de raíces. Fuente: public/img/rosa-raices.png. --}}
    <img class="ra-raices-comunas" src="{{ asset('img/marca/raices-tinta-640.webp') }}"
        srcset="{{ asset('img/marca/raices-tinta-640.webp') }} 640w, {{ asset('img/marca/raices-tinta-900.webp') }} 900w"
        sizes="(min-width: 768px) 560px, 92vw" width="900" height="540" alt="" aria-hidden="true" loading="lazy" decoding="async">
@endif
<div class="ra-comunas">
    @foreach ($comunas as $comuna)
        @php
            $corregimiento = $comuna->esCorregimiento();
            $publicada = (bool) $comuna->pagina?->publicada;
            $numero = $corregimiento ? null : str_pad(ltrim(substr($comuna->codigo, 1), '0'), 2, '0', STR_PAD_LEFT);
            $clases = 'ra-comuna'.($corregimiento ? ' ra-comuna-corregimiento' : '').($publicada ? '' : ' ra-comuna-pronto');
        @endphp
        @if ($publicada)
            <a class="{{ $clases }}" href="{{ route('comunas.show', $comuna) }}" @if ($actual && $actual->is($comuna)) aria-current="true" @endif>
        @else
            <div class="{{ $clases }}">
        @endif
            {{-- En el sitio las comunas se nombran solo por su número; el corregimiento, por su nombre. --}}
            @if ($corregimiento)
                <span class="ra-comuna-barrios">Corregimiento</span>
                <span class="ra-comuna-nombre">El Manzanillo</span>
            @else
                <span class="ra-comuna-num" aria-hidden="true">{{ $numero }}</span>
                <span class="ra-comuna-nombre">{{ $comuna->nombrePublico() }}</span>
            @endif
            @unless ($publicada)<span class="ra-pronto">Pronto</span>@endunless
        @if ($publicada)
            </a>
        @else
            </div>
        @endif
    @endforeach
</div>
