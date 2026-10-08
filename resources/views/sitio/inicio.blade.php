<x-layouts.sitio :datos-estructurados="$datosEstructurados" :descripcion="$pagina?->seo_descripcion">
    @php
        // Secciones por defecto si la página de inicio aún no está armada en el panel.
        $secciones = $secciones->isNotEmpty() ? $secciones : collect(['registro', 'ejes', 'raices', 'comunas', 'buzon', 'noticias', 'agenda', 'redes'])->map(fn ($t) => ['type' => $t, 'data' => []]);
    @endphp
    @foreach ($secciones as $bloque)
        @include('partials.bloque', ['bloque' => $bloque, 'esInicio' => true])
        @if (($bloque['type'] ?? null) === 'registro')
            <x-ra.aviso-escucha />
        @endif
    @endforeach
</x-layouts.sitio>
