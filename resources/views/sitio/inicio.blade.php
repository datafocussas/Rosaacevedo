<x-layouts.sitio :datos-estructurados="$datosEstructurados" :descripcion="$pagina?->seo_descripcion">
    @php
        // Secciones por defecto si la página de inicio aún no está armada en el panel.
        $bloques = $secciones->isNotEmpty() ? $secciones->values()->all()
            : collect(['registro', 'ejes', 'raices', 'comunas', 'buzon', 'noticias', 'agenda', 'redes'])->map(fn ($t) => ['type' => $t, 'data' => []])->all();
    @endphp
    @include('partials.bloques-lista', ['bloques' => $bloques])
</x-layouts.sitio>
