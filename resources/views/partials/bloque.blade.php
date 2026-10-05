{{-- Renderiza un bloque del Builder de Filament: ['type' => …, 'data' => […]]. --}}
@php
    $tipo = $bloque['type'] ?? null;
    $d = $bloque['data'] ?? [];
@endphp
@if ($tipo && view()->exists('partials.bloques.'.$tipo) && ($d['activo'] ?? true))
    @include('partials.bloques.'.$tipo, ['d' => $d])
@endif
