{{-- Redes sociales: perfiles (Facebook, Instagram, TikTok) y publicaciones destacadas, incrustados con los marcos
     oficiales de cada red (App\Support\Incrustacion). Cada marco se carga con el permiso de cookies de redes o con
     un clic. Sin perfiles ni publicaciones válidos no se pinta. --}}
@php
    $redes = \App\Models\RedSocial::query()->where('activa', true)->orderBy('orden')->get();
    $perfiles = collect($d['perfiles'] ?? [])->map(function ($red) use ($redes) {
        $fila = $redes->first(fn ($r) => \App\Support\IconoRed::para($r->icono, $r->url) === $red);

        return $fila ? \App\Support\Incrustacion::perfil($fila->url) : null;
    })->filter()->values();
    $publicaciones = collect($d['publicaciones'] ?? [])->map(fn ($p) => \App\Support\Incrustacion::de(is_array($p) ? ($p['url'] ?? null) : $p))->filter()->values();
@endphp
@if ($perfiles->isNotEmpty() || $publicaciones->isNotEmpty())
<section class="ra-seccion {{ $clasesFondo }}" aria-labelledby="redes-widgets-titulo">
    <div class="ra-contenedor ra-pila-5">
        <div class="ra-pila">
            @if (! empty($d['etiqueta']))<span class="ra-etiqueta">{{ $d['etiqueta'] }}</span>@endif
            <h2 class="ra-h2" id="redes-widgets-titulo">{{ $d['titulo'] ?? 'Lo que pasa en nuestras redes' }}</h2>
            @if (! empty($d['texto']))<p class="ra-entradilla ra-sin-margen">{{ $d['texto'] }}</p>@endif
        </div>
        @if ($perfiles->isNotEmpty())
            <div class="ra-redes-widgets">
                @foreach ($perfiles as $datos)
                    @include('partials.incrustacion', ['datos' => $datos])
                @endforeach
            </div>
        @endif
        @if ($publicaciones->isNotEmpty())
            <div class="ra-redes-widgets ra-redes-publicaciones">
                @foreach ($publicaciones as $datos)
                    @include('partials.incrustacion', ['datos' => $datos])
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif
