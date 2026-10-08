@props(['menu' => collect(), 'redes' => collect()])
@php
    $actual = '/'.ltrim(request()->path(), '/');
    $esActual = fn ($url) => $url === '/' ? $actual === '/' : str_starts_with($actual, rtrim(parse_url($url, PHP_URL_PATH) ?? '', '/'));
@endphp
<a class="ra-saltar" href="#contenido">Saltar al contenido</a>
<header class="ra-encabezado" x-data="{ abierto: false }" @keydown.escape.window="abierto = false">
    <div class="ra-contenedor ra-encabezado-fila">
        <x-ra.marca />
        <nav aria-label="Principal">
            <ul class="ra-menu">
                @foreach ($menu as $item)
                    <li><a href="{{ $item->url }}" @if ($esActual($item->url)) aria-current="page" @endif>{{ $item->texto }}</a></li>
                @endforeach
            </ul>
        </nav>
        <div class="ra-encabezado-acciones">
            <a class="ra-btn ra-btn-principal" href="{{ route('sumate') }}" data-umami-event="sumate_encabezado">Súmate</a>
            <button type="button" class="ra-hamburguesa" :aria-expanded="abierto.toString()" aria-expanded="false" aria-controls="menu-movil" @click="abierto = !abierto">
                <span class="ra-sr" x-text="abierto ? 'Cerrar menú' : 'Abrir menú'">Abrir menú</span>
                <x-ra.icono nombre="menu" x-show="!abierto" />
                <x-ra.icono nombre="cerrar" x-show="abierto" x-cloak />
            </button>
        </div>
    </div>
    <div id="menu-movil" class="ra-menu-movil" x-show="abierto" x-cloak x-transition.opacity @click.outside="abierto = false">
        <div class="ra-contenedor">
            <nav aria-label="Menú móvil">
                <ul class="ra-lista-simple">
                    @foreach ($menu as $item)
                        <li><a href="{{ $item->url }}" @if ($esActual($item->url)) aria-current="page" @endif>{{ $item->texto }}</a></li>
                    @endforeach
                </ul>
            </nav>
            @if ($redes->isNotEmpty())
                <x-ra.redes :redes="$redes" />
            @endif
        </div>
    </div>
</header>
