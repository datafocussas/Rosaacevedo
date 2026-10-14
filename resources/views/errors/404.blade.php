<x-layouts.sitio titulo="Página no encontrada" :no-indexar="true">
    <x-ra.cabecera etiqueta="Error 404" titulo="Esta página no está aquí" entradilla="Puede que el enlace haya cambiado. Te dejamos algunos caminos.">
        <div class="ra-acciones">
            <x-ra.boton :href="route('inicio')">Ir al inicio</x-ra.boton>
            <x-ra.boton :href="route('sumate')" variante="fantasma">Súmate</x-ra.boton>
        </div>
    </x-ra.cabecera>
</x-layouts.sitio>
