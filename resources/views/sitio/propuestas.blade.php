<x-layouts.sitio titulo="Propuestas" descripcion="Los siete ejes de Rosa Acevedo para Itagüí: aquí me planto por la salud, las familias, los jóvenes, los comerciantes, la seguridad, las oportunidades y una ciudad que avanza.">
    <header class="ra-seccion ra-cabecera-pagina">
        <div class="ra-contenedor ra-lectura ra-pila-4">
            <span class="ra-etiqueta">Propuestas</span>
            <h1 class="ra-display">Aquí me planto por…</h1>
            <p class="ra-cuerpo-lg ra-sin-margen">Siete compromisos con Itagüí. Cada uno se construye con lo que nos propones en el buzón ciudadano.</p>
        </div>
    </header>
    <section class="ra-seccion ra-fondo-blanco">
        <div class="ra-contenedor ra-lectura">
            <x-ra.ejes :ejes="$ejes" />
        </div>
    </section>
    @include('partials.bloques.buzon', ['d' => []])
</x-layouts.sitio>
