<section class="ra-seccion-compacta">
    <div class="ra-contenedor">
        @if (! empty($d['titulo']))<h2 class="ra-h2 ra-titulo-seccion">{{ $d['titulo'] }}</h2>@endif
        <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($d['contenido'] ?? '') }}</div>
    </div>
</section>
