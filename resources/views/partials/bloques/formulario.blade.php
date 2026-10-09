{{-- Formulario (diseño v2, §7): versión clara en tarjeta blanca de radio 28. --}}
<section class="ra-seccion {{ $clasesFondo }}">
    <div class="ra-contenedor">
        <div class="ra-angosto ra-centrar">
            @if (($d['tipo'] ?? 'registro') === 'buzon')
                <x-ra.buzon :temas="\App\Models\Tema::query()->where('activo', true)->orderBy('orden')->get()" :barrios="\App\Http\Controllers\RegistroController::barriosAgrupados()" />
            @else
                <x-ra.formulario-registro :titulo="$d['titulo'] ?? 'Súmate a la siembra'" :pasos="2" id="registro-{{ $loop->index ?? 'pagina' }}" />
            @endif
        </div>
    </div>
</section>
