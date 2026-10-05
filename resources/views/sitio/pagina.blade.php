<x-layouts.sitio :titulo="$pagina->seo_titulo ?: $pagina->titulo" :descripcion="$pagina->seo_descripcion" :datos-estructurados="$datosEstructurados ?? null" :no-indexar="$vistaPrevia ?? false">
    @if ($vistaPrevia ?? false)
        <div class="ra-aviso ra-aviso-alerta ra-contenedor" role="note"><x-ra.icono nombre="alerta" /><span>Vista previa: esta página está en estado «{{ $pagina->estado }}».</span></div>
    @endif
    <header class="ra-seccion ra-cabecera-pagina">
        <div class="ra-contenedor ra-lectura ra-pila-4">
            <h1 class="ra-display">{{ $pagina->titulo }}</h1>
            @if ($pagina->slug === 'manifiesto')<x-ra.lema />@endif
        </div>
    </header>
    @foreach ($pagina->bloques ?? [] as $bloque)
        @include('partials.bloque', ['bloque' => $bloque])
    @endforeach
    @if (in_array($pagina->slug, ['manifiesto', 'conoce-a-rosa'], true))
        <div class="ra-contenedor ra-lectura">
            <x-ra.compartir :url="url('/'.$pagina->slug)" :titulo="$pagina->titulo.' · Rosa Acevedo'" />
        </div>
    @endif
    @include('partials.bloques.llamado', ['d' => ['etiqueta' => 'Súmate', 'titulo' => 'Aquí me planto contigo', 'texto' => 'Recibe las propuestas para tu barrio y ayúdanos a construir el programa de gobierno.', 'boton_texto' => 'Súmate']])
</x-layouts.sitio>
