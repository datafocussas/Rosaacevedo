<x-layouts.sitio titulo="Tu comuna" descripcion="Las siete comunas y el corregimiento El Manzanillo de Itagüí (Acuerdo 017 de 2024).">
    <header class="ra-seccion ra-cabecera-pagina">
        <div class="ra-contenedor ra-pila-4">
            <span class="ra-etiqueta">Tu comuna</span>
            <h1 class="ra-display">Aquí me planto en cada barrio</h1>
            <p class="ra-cuerpo-lg ra-sin-margen ra-lectura">Siete comunas y el corregimiento El Manzanillo, según el Acuerdo 017 de 2024.</p>
        </div>
    </header>
    <section class="ra-seccion-compacta">
        <div class="ra-contenedor"><x-ra.selector-comuna :comunas="$comunas" /></div>
    </section>
</x-layouts.sitio>
