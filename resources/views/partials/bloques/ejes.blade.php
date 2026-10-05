@php $ejes ??= \App\Models\Eje::query()->publicados()->get(); @endphp
@if ($ejes->isNotEmpty())
<section class="ra-seccion ra-fondo-blanco" aria-labelledby="ejes-titulo">
    <div class="ra-contenedor ra-dos-columnas">
        <div class="ra-pila-4">
            <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Propuestas' }}</span>
            <h2 class="ra-h2" id="ejes-titulo">{{ $d['titulo'] ?? 'Aquí me planto por…' }}</h2>
            <p class="ra-cuerpo-lg ra-sin-margen">{{ $d['texto'] ?? 'No prometemos veinte obras. Nos comprometemos con un propósito: que ningún joven tenga que irse de Itagüí para cumplir sus sueños.' }}</p>
            <x-ra.boton :href="route('propuestas')" variante="secundario" icono="arrow-right" class="ra-alinear-inicio">{{ $d['boton_texto'] ?? 'Conoce las propuestas' }}</x-ra.boton>
        </div>
        <x-ra.ejes :ejes="$ejes" />
    </div>
</section>
@endif
