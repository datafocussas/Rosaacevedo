@props(['nombre', 'etiqueta', 'tipo' => 'text', 'ayuda' => null, 'opcional' => false, 'valor' => null, 'id' => null])
@php
    $id ??= 'campo-'.str_replace(['[', ']', '.'], '-', $nombre).'-'.\Illuminate\Support\Str::random(4);
    $llave = str_replace(['[', ']'], ['.', ''], $nombre);
    $mensaje = $errors->first($llave);
    $describe = trim(($ayuda ? $id.'-ayuda ' : '').($mensaje ? $id.'-err' : ''));
@endphp
<div class="ra-campo {{ $mensaje ? 'ra-campo-error' : '' }}">
    <label for="{{ $id }}">{{ $etiqueta }} @if ($opcional)<span class="ra-pequeno">(opcional)</span>@endif</label>
    @if ($tipo === 'textarea')
        <textarea id="{{ $id }}" name="{{ $nombre }}" class="ra-textarea" @if ($describe) aria-describedby="{{ $describe }}" @endif @if ($mensaje) aria-invalid="true" @endif {{ $attributes }}>{{ old($llave, $valor) }}</textarea>
    @elseif ($tipo === 'select')
        <select id="{{ $id }}" name="{{ $nombre }}" class="ra-select" @if ($describe) aria-describedby="{{ $describe }}" @endif @if ($mensaje) aria-invalid="true" @endif {{ $attributes }}>{{ $slot }}</select>
    @else
        <input id="{{ $id }}" name="{{ $nombre }}" type="{{ $tipo }}" class="ra-input" value="{{ old($llave, $valor) }}" @if ($describe) aria-describedby="{{ $describe }}" @endif @if ($mensaje) aria-invalid="true" @endif {{ $attributes }}>
    @endif
    @if ($ayuda)<span class="ra-ayuda" id="{{ $id }}-ayuda">{{ $ayuda }}</span>@endif
    @if ($mensaje)<span class="ra-mensaje-error" id="{{ $id }}-err"><x-ra.icono nombre="alerta-circulo" tam="18" /> {{ $mensaje }}</span>@endif
</div>
