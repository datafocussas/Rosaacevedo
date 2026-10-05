@props(['url', 'titulo'])
{{-- Botones de compartir con utm_source de la red (RF-31). --}}
@php
    $con = fn ($fuente) => $url.(str_contains($url, '?') ? '&' : '?').'utm_source='.$fuente.'&utm_medium=compartir';
@endphp
<div class="ra-compartir" x-data="{ copiado: false }">
    <span class="ra-etiqueta">Comparte</span>
    <div class="ra-redes">
        <a class="ra-red" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($con('facebook')) }}" target="_blank" rel="noopener" aria-label="Compartir en Facebook (se abre en una pestaña nueva)" data-conversion="compartir" data-umami-event="compartir" data-umami-event-red="facebook"><x-ra.icono nombre="facebook" /></a>
        <a class="ra-red" href="https://wa.me/?text={{ urlencode($titulo.' '.$con('whatsapp')) }}" target="_blank" rel="noopener" aria-label="Compartir por WhatsApp (se abre en una pestaña nueva)" data-conversion="compartir" data-umami-event="compartir" data-umami-event-red="whatsapp"><x-ra.icono nombre="whatsapp" /></a>
        <a class="ra-red" href="https://x.com/intent/post?text={{ urlencode($titulo) }}&url={{ urlencode($con('x')) }}" target="_blank" rel="noopener" aria-label="Compartir en X (se abre en una pestaña nueva)" data-conversion="compartir" data-umami-event="compartir" data-umami-event-red="x"><x-ra.icono nombre="x" /></a>
        <button type="button" class="ra-red ra-red-boton" @click="navigator.clipboard.writeText(@js($con('copiado'))); copiado = true; setTimeout(() => copiado = false, 3000)" aria-label="Copiar enlace"><x-ra.icono nombre="enlace" /></button>
    </div>
    <span class="ra-pequeno" role="status" x-show="copiado" x-cloak>Enlace copiado.</span>
</div>
