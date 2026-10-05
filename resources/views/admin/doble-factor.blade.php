<!doctype html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Doble factor · Panel Rosa Acevedo</title>
    @vite(['resources/css/sitio.css'])
</head>
<body class="ra">
<main id="contenido" class="ra-seccion">
    <div class="ra-contenedor ra-angosto">
        <form class="ra-form" method="POST" action="{{ route('dos-factores.verificar') }}">
            @csrf
            <x-ra.marca />
            <h1 class="ra-h3">{{ $configurando ? 'Activa el doble factor' : 'Verificación en dos pasos' }}</h1>
            @if ($configurando)
                <p class="ra-sin-margen">El panel exige doble factor. Escanea este código con una aplicación de autenticación (Google Authenticator, Microsoft Authenticator, Authy) y escribe el código de 6 dígitos.</p>
                <div class="ra-qr">{!! $qr !!}</div>
                <p class="ra-pequeno ra-sin-margen">Si no puedes escanear, escribe esta clave: <code>{{ trim(chunk_split($secreto, 4, ' ')) }}</code></p>
            @else
                <p class="ra-sin-margen">Escribe el código de 6 dígitos de tu aplicación de autenticación.</p>
            @endif
            <x-ra.campo nombre="codigo" etiqueta="Código" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" required autofocus />
            <button type="submit" class="ra-btn ra-btn-principal ra-btn-bloque">Verificar</button>
        </form>
        <form method="POST" action="{{ route('filament.admin.auth.logout') }}" style="margin-top: var(--space-4); text-align: center">
            @csrf
            <button type="submit" class="ra-enlace-boton" style="color: var(--azul-itagui)">Salir</button>
        </form>
    </div>
</main>
</body>
</html>
