<x-layouts.sitio titulo="Gracias por sumarte" :no-indexar="true">
    <section class="ra-seccion">
        <div class="ra-contenedor ra-angosto ra-pila-4">
            <x-ra.lema />
            <h1 class="ra-display">Gracias por sumarte</h1>
            <div class="ra-aviso ra-aviso-exito" role="status"><x-ra.icono nombre="exito" /><span>Tu código es <strong>{{ $codigo }}</strong>. Te escribiremos con lo que pasa en tu comuna.</span></div>
            @if ($whatsapp)
                <a class="ra-btn ra-btn-whatsapp ra-btn-bloque" href="{{ $whatsapp }}" target="_blank" rel="noopener" data-conversion="whatsapp_clic" data-umami-event="whatsapp_clic"><x-ra.icono nombre="whatsapp" /> Escríbenos por WhatsApp</a>
            @endif
            <x-ra.boton :href="route('buzon')" variante="secundario" :bloque="true">Deja tu propuesta para tu barrio</x-ra.boton>
        </div>
    </section>
</x-layouts.sitio>
