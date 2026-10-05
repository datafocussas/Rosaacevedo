<x-layouts.sitio titulo="Página no encontrada" :no-indexar="true">
    <section class="ra-seccion">
        <div class="ra-contenedor ra-lectura ra-pila-4">
            <span class="ra-etiqueta">Error 404</span>
            <h1 class="ra-display">Esta página no está aquí</h1>
            <p class="ra-cuerpo-lg ra-sin-margen">Puede que el enlace haya cambiado. Te dejamos algunos caminos.</p>
            <div class="ra-banner-acciones">
                <x-ra.boton :href="route('inicio')" variante="secundario">Ir al inicio</x-ra.boton>
                <x-ra.boton :href="route('sumate')">Súmate</x-ra.boton>
            </div>
        </div>
    </section>
</x-layouts.sitio>
