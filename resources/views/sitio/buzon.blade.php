<x-layouts.sitio titulo="Buzón ciudadano" descripcion="Cuéntanos qué necesita tu barrio. Tu propuesta entra al programa de gobierno de Rosa Acevedo.">
    <section class="ra-seccion">
        <div class="ra-contenedor ra-dos-columnas">
            <div class="ra-pila-4">
                <span class="ra-etiqueta">Buzón ciudadano</span>
                <h1 class="ra-display">Tu propuesta entra al programa de gobierno</h1>
                <p class="ra-cuerpo-lg ra-sin-margen">Cuéntanos qué necesita tu barrio. Leemos cada propuesta, la clasificamos por tema y comuna, y te contamos qué pasó con ella.</p>
            </div>
            <x-ra.buzon :temas="$temas" :barrios="$barrios" :tema-id="$temaId" />
        </div>
    </section>
</x-layouts.sitio>
