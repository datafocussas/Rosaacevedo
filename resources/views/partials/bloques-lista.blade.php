{{-- Lista de bloques del Builder con fondos resueltos (diseño v2, §7 y §8.3).
     Variables: $bloques (array), $anterior (fondo de la sección previa, opcional), $despuesDe (callback opcional). --}}
@php
    $visibles = array_values(array_filter($bloques ?? [], fn ($b) => ($b['data']['activo'] ?? true) && view()->exists('partials.bloques.'.($b['type'] ?? '_'))));
    $resueltos = \App\Support\Fondos::resolver($visibles, $anterior ?? null);
@endphp
@foreach ($resueltos as $r)
    @if ($r['separador'])<div class="ra-separador-fondo" aria-hidden="true"></div>@endif
    @include('partials.bloques.'.$r['bloque']['type'], ['d' => $r['bloque']['data'] ?? [], 'fondo' => $r['fondo'], 'clasesFondo' => \App\Support\Fondos::clases($r['fondo'])])
    @if (($r['bloque']['type'] ?? null) === 'registro')
        <x-ra.aviso-escucha />
    @endif
@endforeach
