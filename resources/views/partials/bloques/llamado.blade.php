<section class="ra-seccion ra-inverso">
    <div class="ra-contenedor ra-fila-llamado">
        <div class="ra-lectura">
            @if (! empty($d['etiqueta']))<span class="ra-etiqueta">{{ $d['etiqueta'] }}</span>@endif
            <h2 class="ra-h2 ra-titulo-seccion-corto">{{ $d['titulo'] ?? 'Súmate' }}</h2>
            @if (! empty($d['texto']))<p class="ra-sin-margen">{{ $d['texto'] }}</p>@endif
        </div>
        <x-ra.boton :href="$d['boton_url'] ?? route('sumate')" icono="arrow-right">{{ $d['boton_texto'] ?? 'Súmate' }}</x-ra.boton>
    </div>
</section>
