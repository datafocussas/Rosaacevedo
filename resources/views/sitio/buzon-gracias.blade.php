<x-layouts.sitio titulo="Propuesta recibida" :no-indexar="true">
    <section class="ra-seccion">
        <div class="ra-contenedor ra-angosto ra-pila-4">
            <h1 class="ra-display">Propuesta recibida</h1>
            <div class="ra-aviso ra-aviso-exito" role="status"><x-ra.icono nombre="exito" /><span>Tu código es <strong>{{ $codigo }}</strong>. Te escribiremos cuando la revisemos.</span></div>
            <x-ra.boton :href="route('sumate')" :bloque="true">Súmate a la siembra</x-ra.boton>
            <x-ra.boton :href="route('buzon')" variante="fantasma" :bloque="true">Enviar otra propuesta</x-ra.boton>
        </div>
    </section>
</x-layouts.sitio>
