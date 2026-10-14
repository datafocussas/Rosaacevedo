@props(['etiqueta' => null, 'titulo', 'entradilla' => null, 'imagen' => null, 'alt' => '', 'foco' => null, 'nivel' => 'h1'])
{{-- Cabecera de páginas internas (diseño v2, §5.2): noche, rótulo oro, h1 marfil, entradilla niebla y, si hay imagen,
     la foto con tratamiento cinematográfico a la derecha (420 px; en móvil debajo, 4:3). Slot «antes» para volver, insignias…
     Slot por defecto para contenido extra (lema, botones). --}}
<header {{ $attributes->merge(['class' => 'ra-cabecera ra-fondo-noche ra-oscuro'.($imagen ? ' ra-cabecera-con-foto' : '')]) }}>
    <div class="ra-contenedor">
        <div class="ra-cabecera-rejilla">
            <div class="ra-cabecera-texto">
                {{ $antes ?? '' }}
                @if ($etiqueta)<span class="ra-etiqueta">{{ $etiqueta }}</span>@endif
                <{{ $nivel }} class="ra-display">{{ $titulo }}</{{ $nivel }}>
                @if ($entradilla)<p class="ra-entradilla">{{ $entradilla }}</p>@endif
                {{ $slot }}
            </div>
            @if ($imagen)
                <div class="ra-cabecera-foto">
                    <img src="{{ $imagen }}" alt="{{ $alt }}" @if ($foco) style="object-position: {{ $foco }}" @endif fetchpriority="high" decoding="async" width="1200" height="900">
                </div>
            @endif
        </div>
    </div>
</header>
