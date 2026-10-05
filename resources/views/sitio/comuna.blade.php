<x-layouts.sitio :titulo="$comuna->rotulo().' · '.$comuna->nombreCorto()" :descripcion="'Propuestas, agenda y noticias de Rosa Acevedo en '.$comuna->nombre.'.'">
    @if ($banners->isNotEmpty())
        <x-ra.banner :banners="$banners" />
    @endif
    <section class="ra-seccion">
        <div class="ra-contenedor ra-dos-columnas">
            <div class="ra-pila-4">
                <span class="ra-etiqueta">{{ $comuna->rotulo() }}</span>
                <h1 class="ra-display">{{ $comuna->nombreCorto() }}</h1>
                @if ($pagina?->saludo)
                    <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($pagina->saludo) }}</div>
                @else
                    <p class="ra-cuerpo-lg ra-sin-margen">Cuéntanos qué necesita tu barrio. Con tu registro te contamos lo que pasa en {{ $comuna->esCorregimiento() ? 'el corregimiento' : 'tu comuna' }}.</p>
                @endif
                @if ($whatsapp)
                    <a class="ra-btn ra-btn-whatsapp ra-btn-izquierda" href="{{ $whatsapp }}" target="_blank" rel="noopener" data-conversion="whatsapp_clic" data-umami-event="whatsapp_clic"><x-ra.icono nombre="whatsapp" /> Escríbenos por WhatsApp</a>
                @endif
            </div>
            <x-ra.formulario-registro :titulo="'Súmate en '.$comuna->nombreCorto()" :comuna-id="$comuna->id" :pasos="2" id="registro-comuna" />
        </div>
    </section>

    @foreach ($pagina?->bloques ?? [] as $bloque)
        @include('partials.bloque', ['bloque' => $bloque])
    @endforeach

    @if ($destacadas->isNotEmpty())
        <section class="ra-seccion ra-fondo-blanco" aria-labelledby="destacadas">
            <div class="ra-contenedor">
                <h2 class="ra-h2 ra-titulo-seccion" id="destacadas">Lo que nos han propuesto aquí</h2>
                <div class="ra-grilla ra-grilla-2">
                    @foreach ($destacadas as $propuesta)
                        <blockquote class="ra-cita"><p>{{ \Illuminate\Support\Str::limit($propuesta->texto, 280) }}</p><footer>{{ $propuesta->tema?->nombre }} · {{ \App\Models\PropuestaCiudadana::ESTADOS[$propuesta->estado] }}</footer></blockquote>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('partials.bloques.agenda', ['d' => ['titulo' => 'Encuentros en '.$comuna->nombreCorto()]])
    @include('partials.bloques.noticias', ['d' => ['titulo' => 'Noticias de '.$comuna->nombreCorto()]])

    @if ($barrios->isNotEmpty())
        <section class="ra-seccion-compacta" aria-labelledby="barrios">
            <div class="ra-contenedor">
                <h2 class="ra-h3 ra-titulo-seccion" id="barrios">{{ $comuna->esCorregimiento() ? 'Sectores y veredas' : 'Barrios' }}</h2>
                <p class="ra-lista-barrios">{{ $barrios->pluck('nombre')->join(' · ') }}</p>
            </div>
        </section>
    @endif

    <section class="ra-seccion" aria-labelledby="otras">
        <div class="ra-contenedor">
            <h2 class="ra-h3 ra-titulo-seccion" id="otras">Otras comunas</h2>
            <x-ra.selector-comuna :comunas="$comunas" :actual="$comuna" />
        </div>
    </section>
</x-layouts.sitio>
