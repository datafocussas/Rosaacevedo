@php
    $esManifiesto = $pagina->slug === 'manifiesto';
    $imagenOg = $pagina->getFirstMedia('og');
@endphp
<x-layouts.sitio :titulo="$pagina->seo_titulo ?: $pagina->titulo" :descripcion="$pagina->seo_descripcion" :imagen-redes="$imagenOg ? \App\Support\Medios::url($imagenOg) : null" :datos-estructurados="$datosEstructurados ?? null" :no-indexar="$vistaPrevia ?? false">
    <x-ra.cabecera :titulo="$pagina->titulo" :entradilla="$pagina->seo_descripcion" :etiqueta="$esManifiesto ? 'Manifiesto' : null"
        :imagen="\App\Support\Medios::url($imagenOg)" :foco="$imagenOg ? \App\Support\Medios::foco($imagenOg) : null">
        @if ($vistaPrevia ?? false)
            <x-slot:antes><div class="ra-aviso ra-aviso-alerta" role="note"><x-ra.icono nombre="alerta" /><span>Vista previa: esta página está en estado «{{ $pagina->estado }}».</span></div></x-slot:antes>
        @endif
        @if ($esManifiesto)<x-ra.lema />@endif
    </x-ra.cabecera>
    @include('partials.bloques-lista', ['bloques' => $pagina->bloques ?? [], 'anterior' => 'noche'])
    @if (in_array($pagina->slug, ['manifiesto', 'conoce-a-rosa'], true))
        <div class="ra-fondo-marfil"><div class="ra-contenedor ra-lectura-centrada ra-pie-compartir">
            <x-ra.compartir :url="url('/'.$pagina->slug)" :titulo="$pagina->titulo.' · Rosa Acevedo'" />
        </div></div>
    @endif
    @include('partials.bloques.llamado', ['d' => ['etiqueta' => 'Súmate', 'titulo' => 'Aquí me planto contigo', 'texto' => 'Recibe las propuestas para tu barrio y ayúdanos a construir el programa de gobierno.', 'boton_texto' => 'Súmate'], 'clasesFondo' => 'ra-fondo-esmeralda ra-oscuro'])
</x-layouts.sitio>
