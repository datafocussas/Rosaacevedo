<x-layouts.sitio titulo="Propuestas" descripcion="Los siete ejes de Rosa Acevedo para Itagüí: aquí me planto por la salud, las familias, los jóvenes, los comerciantes, la seguridad, las oportunidades y una ciudad que avanza.">
    <x-ra.cabecera etiqueta="Propuestas" titulo="Aquí me planto por…" entradilla="Siete compromisos con Itagüí. Cada uno se construye con lo que nos propones en el buzón ciudadano." />
    <section class="ra-seccion ra-fondo-marfil">
        <div class="ra-contenedor"><x-ra.ejes :ejes="$ejes" /></div>
    </section>
    @include('partials.bloques.buzon', ['d' => [], 'clasesFondo' => 'ra-fondo-esmeralda ra-oscuro'])
</x-layouts.sitio>
