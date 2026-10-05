@props(['href' => null, 'variante' => 'principal', 'icono' => null, 'iconoInicio' => null, 'bloque' => false, 'type' => 'submit'])
@php
    $clases = 'ra-btn ra-btn-'.$variante.($bloque ? ' ra-btn-bloque' : '');
@endphp
@if ($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $clases]) }}>@if ($iconoInicio)<x-ra.icono :nombre="$iconoInicio" />@endif{{ $slot }}@if ($icono)<x-ra.icono :nombre="$icono" />@endif</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => $clases]) }}>@if ($iconoInicio)<x-ra.icono :nombre="$iconoInicio" />@endif{{ $slot }}@if ($icono)<x-ra.icono :nombre="$icono" />@endif</button>
@endif
