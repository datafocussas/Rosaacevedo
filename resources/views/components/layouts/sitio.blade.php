@props(['titulo' => null, 'descripcion' => null, 'imagenRedes' => null, 'canonica' => null, 'tipoOg' => 'website', 'datosEstructurados' => null, 'noIndexar' => false])
@php
    $ajustes = app(\App\Services\Ajustes::class);
    $titulo = $titulo ? $titulo.' · Rosa Acevedo' : 'Rosa Acevedo · Por el futuro de Itagüí';
    $descripcion = $descripcion ?: 'Sitio oficial de Rosa María Acevedo Jaramillo. Aquí me planto por el futuro de Itagüí.';
    $imagenRedes = $imagenRedes ?: ($ajustes->get('imagen_redes') ? asset('storage/'.$ajustes->get('imagen_redes')) : asset('img/redes-por-defecto.jpg'));
    $canonica = $canonica ?: url()->current();
@endphp
<!doctype html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }}</title>
    <meta name="description" content="{{ $descripcion }}">
    <link rel="canonical" href="{{ $canonica }}">
    @if (app()->environment('pruebas', 'staging') || $noIndexar)
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta property="og:site_name" content="Rosa Acevedo">
    <meta property="og:locale" content="es_CO">
    <meta property="og:type" content="{{ $tipoOg }}">
    <meta property="og:title" content="{{ $titulo }}">
    <meta property="og:description" content="{{ $descripcion }}">
    <meta property="og:url" content="{{ $canonica }}">
    <meta property="og:image" content="{{ $imagenRedes }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@rosaacevedoj">
    <meta name="theme-color" content="#04151f">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @vite(['resources/css/sitio.css', 'resources/js/sitio.js'])
    @if (filled(config('rosa.umami.url')) && filled(config('rosa.umami.website_id')))
        <script defer src="{{ rtrim(config('rosa.umami.url'), '/') }}/script.js" data-website-id="{{ config('rosa.umami.website_id') }}" data-domains="{{ config('rosa.umami.dominios') }}"></script>
    @endif
    @if (filled(config('rosa.turnstile.site_key')))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    @if ($datosEstructurados)
        <script type="application/ld+json">{!! json_encode($datosEstructurados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endif
</head>
<body class="ra" data-variante="{{ request()->attributes->get('variante') }}" data-api="{{ url('/api/v1') }}">
    <x-ra.encabezado :menu="$menuPrincipal" :redes="$redes" />
    <main id="contenido" tabindex="-1">
        {{ $slot }}
    </main>
    <x-ra.pie :redes="$redes" :menu-sitio="$menuPieSitio" :menu-transparencia="$menuPieTransparencia" />
    @include('partials.cookies')
</body>
</html>
