@props(['redes' => collect(), 'menuSitio' => collect(), 'menuTransparencia' => collect()])
{{-- Pie (diseño v2, §6.12): profundo, tres columnas con rótulos oro y enlaces oro claro; «Itagüí» gigante en trazo oro,
     decorativo; línea legal (responsable del tratamiento y financiación en modo campaña). --}}
@php
    $ajustes = app(\App\Services\Ajustes::class);
    $responsable = $ajustes->get('responsable_tratamiento', []);
    $contacto = $ajustes->get('contacto_email') ?? ($responsable['email'] ?? null);
    $leyenda = $ajustes->get('pie_leyenda_financiacion');
@endphp
<footer class="ra-pie ra-fondo-profundo ra-oscuro">
    <div class="ra-contenedor">
        <div class="ra-pie-grid">
            <div class="ra-pila-4">
                <x-ra.marca />
                <p class="ra-pie-frase">{{ $ajustes->get('pie_frase', 'Mis raíces están aquí. Y las raíces no se trasplantan.') }}</p>
                @if ($redes->isNotEmpty())<x-ra.redes :redes="$redes" variante="iconos" />@endif
            </div>
            <nav aria-labelledby="pie-sitio">
                <p class="ra-etiqueta ra-pie-titulo" id="pie-sitio">El sitio</p>
                <ul class="ra-lista-simple">
                    @foreach ($menuSitio as $item)<li><a href="{{ $item->url }}">{{ $item->texto }}</a></li>@endforeach
                </ul>
            </nav>
            <nav aria-labelledby="pie-transparencia">
                <p class="ra-etiqueta ra-pie-titulo" id="pie-transparencia">Transparencia</p>
                <ul class="ra-lista-simple">
                    @foreach ($menuTransparencia as $item)<li><a href="{{ $item->url }}">{{ $item->texto }}</a></li>@endforeach
                </ul>
            </nav>
        </div>
        <div class="ra-pie-legal">
            Sitio oficial de Rosa María Acevedo Jaramillo · Itagüí, Antioquia
            · Responsable del tratamiento: {{ $responsable['nombre'] ?? '[POR CONFIRMAR]' }} ({{ $responsable['identificacion'] ?? '[POR CONFIRMAR]' }})
            @if ($contacto) · <a href="mailto:{{ $contacto }}">{{ $contacto }}</a>@endif
            @if (! $ajustes->enPrecampana() && $leyenda)<br>{{ $leyenda }}@endif
            <br><button type="button" class="ra-enlace-boton" data-abrir-cookies>Preferencias de cookies</button>
            <span class="ra-pie-credito">Desarrollo: <a href="https://www.datafocussas.com" target="_blank" rel="noopener">DataFocus S.A.S.<span class="ra-sr"> (abre en una pestaña nueva)</span></a></span>
        </div>
    </div>
    <span class="ra-pie-itagui" aria-hidden="true">Itagüí</span>
</footer>
