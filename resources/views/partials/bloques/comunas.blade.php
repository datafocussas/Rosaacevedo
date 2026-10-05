@php $comunas ??= \App\Http\Controllers\SitioController::comunasConConteo(); @endphp
<section class="ra-seccion" aria-labelledby="comunas-titulo">
    <div class="ra-contenedor">
        <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Tu comuna' }}</span>
        <h2 class="ra-h2 ra-titulo-seccion" id="comunas-titulo">{{ $d['titulo'] ?? 'Aquí me planto en cada barrio' }}</h2>
        <x-ra.selector-comuna :comunas="$comunas" />
    </div>
</section>
