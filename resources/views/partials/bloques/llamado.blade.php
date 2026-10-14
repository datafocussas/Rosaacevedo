{{-- Llamado a la acción (diseño v2, §7): esmeralda, noche o coral (en coral, texto noche y botón noche). --}}
<section class="ra-seccion {{ $clasesFondo ?? 'ra-fondo-esmeralda ra-oscuro' }}">
    <div class="ra-contenedor ra-llamado">
        <div class="ra-llamado-texto" data-aparecer>
            @if (! empty($d['etiqueta']))<span class="ra-etiqueta">{{ $d['etiqueta'] }}</span>@endif
            <h2 class="ra-h2-medio">{{ $d['titulo'] ?? 'Súmate' }}</h2>
            @if (! empty($d['texto']))<p class="ra-sin-margen">{{ $d['texto'] }}</p>@endif
        </div>
        <x-ra.boton :href="$d['boton_url'] ?? route('sumate')" icono="arrow-right">{{ $d['boton_texto'] ?? 'Súmate' }}</x-ra.boton>
    </div>
</section>
