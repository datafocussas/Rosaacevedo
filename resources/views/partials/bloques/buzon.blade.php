<section class="ra-seccion ra-fondo-coral" aria-labelledby="buzon-llamado">
    <div class="ra-contenedor ra-fila-llamado">
        <div class="ra-lectura">
            <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Buzón ciudadano' }}</span>
            <h2 class="ra-h2 ra-titulo-seccion-corto" id="buzon-llamado">{{ $d['titulo'] ?? '¿Qué necesita tu barrio?' }}</h2>
            <p class="ra-sin-margen">{{ $d['texto'] ?? 'Tu propuesta entra al programa de gobierno. Te contamos qué pasó con ella.' }}</p>
        </div>
        <x-ra.boton :href="route('buzon')" icono="arrow-right">{{ $d['boton_texto'] ?? 'Deja tu propuesta' }}</x-ra.boton>
    </div>
</section>
