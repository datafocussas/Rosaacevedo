{{-- Un bloque suelto (compatibilidad): usa el fondo elegido o el de por defecto. --}}
@php $fondo = \App\Support\Fondos::elegido($bloque); @endphp
@if (($bloque['data']['activo'] ?? true) && view()->exists('partials.bloques.'.($bloque['type'] ?? '_')))
    @include('partials.bloques.'.$bloque['type'], ['d' => $bloque['data'] ?? [], 'fondo' => $fondo, 'clasesFondo' => \App\Support\Fondos::clases($fondo)])
@endif
