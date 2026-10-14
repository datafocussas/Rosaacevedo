<section class="ra-seccion-compacta {{ $clasesFondo }}">
    <div class="ra-contenedor">
        <div class="ra-lectura-centrada ra-pila-4">
            @if (! empty($d['titulo']))<h2 class="ra-h2-medio">{{ $d['titulo'] }}</h2>@endif
            <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($d['contenido'] ?? '') }}</div>
        </div>
    </div>
</section>
