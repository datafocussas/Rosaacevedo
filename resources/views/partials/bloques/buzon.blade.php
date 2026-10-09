{{-- Buzón (diseño v2, §6.7): esmeralda; texto a la izquierda y el formulario en cápsula con chips de tema. --}}
@php
    $temas ??= \App\Models\Tema::query()->where('activo', true)->orderBy('orden')->get();
    $barrios ??= \App\Http\Controllers\RegistroController::barriosAgrupados();
    $titulo = $d['titulo'] ?? '¿Qué necesita tu barrio?';
@endphp
<section class="ra-seccion {{ $clasesFondo }}" aria-labelledby="buzon-llamado">
    <div class="ra-contenedor ra-buzon-rejilla">
        <div class="ra-pila-5" data-aparecer>
            <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Buzón ciudadano' }}</span>
            <h2 class="ra-h2" id="buzon-llamado">{{ $titulo }}</h2>
            <p class="ra-entradilla">{{ $d['texto'] ?? 'Tu propuesta entra al programa de gobierno. Te contamos qué pasó con ella.' }}</p>
        </div>
        @if ($temas->isNotEmpty())
            <x-ra.buzon :temas="$temas" :barrios="$barrios" variante="oscuro" titulo="Tu propuesta" />
        @else
            <x-ra.boton :href="route('buzon')" icono="arrow-right">{{ $d['boton_texto'] ?? 'Deja tu propuesta' }}</x-ra.boton>
        @endif
    </div>
</section>
