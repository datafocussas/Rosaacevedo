<x-layouts.sitio titulo="Propuesta recibida" :no-indexar="true">
    <x-ra.cabecera etiqueta="Buzón ciudadano" titulo="Propuesta recibida" />
    <section class="ra-seccion ra-fondo-marfil">
        <div class="ra-contenedor"><div class="ra-angosto ra-centrar ra-pila-4">
            <div class="ra-aviso ra-aviso-exito" role="status"><x-ra.icono nombre="exito" /><span>Tu código es <strong>{{ $codigo }}</strong>. Te escribiremos cuando la revisemos.</span></div>
            <x-ra.boton :href="route('sumate')" :bloque="true">Súmate a la siembra</x-ra.boton>
            <x-ra.boton :href="route('buzon')" variante="fantasma" :bloque="true">Enviar otra propuesta</x-ra.boton>
        </div></div>
    </section>
</x-layouts.sitio>
