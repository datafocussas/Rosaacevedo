@props(['tipo', 'nombre', 'obligatorio' => false, 'nota' => null, 'error' => null])
{{-- Casilla de autorización (Consentimiento): separada, nunca premarcada, con el texto exacto de la versión vigente. --}}
@php
    $politica = app(\App\Services\TextosLegales::class)->vigente($tipo);
    $id = 'consent-'.$tipo.'-'.\Illuminate\Support\Str::random(4);
    $campo = 'consent.'.$nombre;
    $mensaje = $error ?? $errors->first($campo);
@endphp
<div class="ra-campo {{ $mensaje ? 'ra-campo-error' : '' }}">
    <label class="ra-check {{ $obligatorio ? '' : 'ra-check-opcional' }}" for="{{ $id }}">
        <input type="checkbox" id="{{ $id }}" name="consent[{{ $nombre }}]" value="1"
            @if ($obligatorio) required aria-required="true" @endif
            @if ($mensaje) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif
            {{ $attributes }}>
        <span>
            {{ $politica?->texto ?? '[POR CONFIRMAR] Texto de autorización pendiente de aprobación jurídica.' }}
            @if ($obligatorio)
                Lee la <a href="{{ route('politica-de-datos') }}" target="_blank">política de tratamiento de datos</a>@if ($politica) (versión {{ $politica->version }})@endif.
            @endif
            @if ($nota)<span class="ra-pequeno">{{ $nota }}</span>@endif
        </span>
    </label>
    <input type="hidden" name="consent_version[{{ $nombre }}]" value="{{ $politica?->id }}">
    @if ($mensaje)
        <span class="ra-mensaje-error" id="{{ $id }}-err"><x-ra.icono nombre="alerta-circulo" tam="18" /> {{ $mensaje }}</span>
    @endif
</div>
