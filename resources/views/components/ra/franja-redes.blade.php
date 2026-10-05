@props(['redes', 'hashtag' => '#AquíMePlantoPorItagüí'])
{{-- Ecosistema de redes y WhatsApp con código de origen (FranjaRedes). --}}
@php
    $ajustes = app(\App\Services\Ajustes::class);
    $whatsapp = $ajustes->enlaceWhatsapp(session('origen.codigo_q') ? 'Q-'.session('origen.codigo_q') : 'WEB');
    $canal = $ajustes->get('whatsapp_canal');
@endphp
<section class="ra-inverso ra-seccion" aria-labelledby="redes-titulo">
    <div class="ra-contenedor ra-dos-columnas ra-centrado">
        <div class="ra-pila">
            <span class="ra-etiqueta">{{ $ajustes->get('hashtag', $hashtag) }}</span>
            <h2 class="ra-h2" id="redes-titulo">Conversemos donde tú estás</h2>
            <p class="ra-sin-margen ra-texto-suave-inverso">Síguenos, comparte y escríbenos. Cada mensaje llega a una persona del equipo.</p>
            <x-ra.redes :redes="$redes" />
        </div>
        <div class="ra-pila">
            @if ($whatsapp)
                <a class="ra-btn ra-btn-whatsapp ra-btn-izquierda" href="{{ $whatsapp }}" target="_blank" rel="noopener" data-conversion="whatsapp_clic" data-umami-event="whatsapp_clic"><x-ra.icono nombre="whatsapp" /> Escríbenos por WhatsApp</a>
            @endif
            @if ($canal)
                <a class="ra-btn ra-btn-fantasma ra-btn-izquierda" href="{{ $canal }}" target="_blank" rel="noopener" data-umami-event="whatsapp_canal">Sigue el canal de Rosa en WhatsApp</a>
                <span class="ra-pequeno">El canal de WhatsApp es de solo lectura; nadie ve tu número.</span>
            @endif
        </div>
    </div>
</section>
