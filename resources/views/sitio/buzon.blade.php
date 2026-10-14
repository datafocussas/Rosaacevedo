<x-layouts.sitio titulo="Buzón ciudadano" descripcion="Cuéntanos qué necesita tu barrio. Tu propuesta entra al programa de gobierno de Rosa Acevedo.">
    <x-ra.cabecera etiqueta="Buzón ciudadano" titulo="Tu propuesta entra al programa de gobierno"
        entradilla="Cuéntanos qué necesita tu barrio. Leemos cada propuesta, la clasificamos por tema y comuna, y te contamos qué pasó con ella." />
    <section class="ra-seccion ra-fondo-marfil">
        <div class="ra-contenedor"><div class="ra-angosto ra-centrar">
            <x-ra.buzon :temas="$temas" :barrios="$barrios" :tema-id="$temaId" :comuna-id="$comunaId" />
        </div></div>
    </section>
</x-layouts.sitio>
