@props(['redes', 'hashtag' => '#AquíMePlanto'])
{{-- Redes (diseño v2, §6.10): noche, centrado; hashtag vigente en grande con la última palabra en coral (se parte en la
     última mayúscula); píldoras por red en el orden del panel; canal de WhatsApp en jade. --}}
@php
    $ajustes = app(\App\Services\Ajustes::class);
    $etiqueta = trim((string) $ajustes->get('hashtag', $hashtag));
    preg_match('/^(.*?)(\p{Lu}[^\p{Lu}]*)$/u', $etiqueta, $partes);
    $inicio = $partes[1] ?? '';
    $final = $partes[2] ?? '';
    $partir = $inicio !== '' && $inicio !== '#';
    $whatsapp = $ajustes->enlaceWhatsapp(session('origen.codigo_q') ? 'Q-'.session('origen.codigo_q') : 'WEB');
    $canal = $ajustes->get('whatsapp_canal');
@endphp
<div class="ra-redes-franja">
    <span class="ra-etiqueta">Conversemos donde tú estás</span>
    <p class="ra-hashtag {{ mb_strlen($etiqueta) > 16 ? 'ra-hashtag-largo' : '' }}">
        @if ($partir){{ $inicio }}<span class="ra-acento">{{ $final }}</span>@else{{ $etiqueta }}@endif
    </p>
    <x-ra.redes :redes="$redes">
        @if ($whatsapp)
            <a class="ra-red-pildora" href="{{ $whatsapp }}" target="_blank" rel="noopener" data-conversion="whatsapp_clic" data-umami-event="whatsapp_clic"><x-ra.icono nombre="whatsapp" /> Escríbenos por WhatsApp</a>
        @endif
        @if ($canal)
            <a class="ra-btn ra-btn-jade ra-btn-compacto" href="{{ $canal }}" target="_blank" rel="noopener" data-umami-event="whatsapp_canal">Sigue el canal de WhatsApp</a>
        @endif
    </x-ra.redes>
    @if ($canal)<p class="ra-pequeno ra-sin-margen">El canal de WhatsApp es de solo lectura; nadie ve tu número.</p>@endif
</div>
