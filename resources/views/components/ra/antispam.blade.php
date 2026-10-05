@props(['comunaId' => null, 'eventoId' => null])
{{-- Origen del envío (RF-03), campo trampa y Cloudflare Turnstile (RF-05). --}}
@php $origen = session('origen', []); @endphp
<div class="ra-trampa" aria-hidden="true">
    <label>No llenes este campo <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label>
</div>
@foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'codigo_q', 'referrer'] as $campo)
    <input type="hidden" name="origen[{{ $campo }}]" value="{{ $origen[$campo] ?? '' }}" data-origen="{{ $campo }}">
@endforeach
<input type="hidden" name="origen[pagina]" value="{{ '/'.ltrim(request()->path(), '/') }}">
<input type="hidden" name="origen[comuna_pagina]" value="{{ $comunaId ?? ($origen['comuna_pagina'] ?? '') }}">
<input type="hidden" name="origen[evento]" value="{{ $eventoId ?? ($origen['evento_id'] ?? '') }}">
@if (filled(config('rosa.turnstile.site_key')))
    <div class="cf-turnstile" data-sitekey="{{ config('rosa.turnstile.site_key') }}" data-language="es" data-size="flexible"></div>
@endif
@error('turnstile')
    <span class="ra-mensaje-error"><x-ra.icono nombre="alerta-circulo" tam="18" /> {{ $message }}</span>
@enderror
