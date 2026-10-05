<section class="ra-seccion">
    <div class="ra-contenedor ra-angosto">
        @if (($d['tipo'] ?? 'registro') === 'buzon')
            <x-ra.buzon :temas="\App\Models\Tema::query()->where('activo', true)->orderBy('orden')->get()" :barrios="\App\Http\Controllers\RegistroController::barriosAgrupados()" />
        @else
            <x-ra.formulario-registro :titulo="$d['titulo'] ?? 'Súmate a la siembra'" :pasos="2" id="registro-{{ $loop->index ?? 'pagina' }}" />
        @endif
    </div>
</section>
