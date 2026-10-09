<x-layouts.sitio :titulo="$comuna->rotulo().' · '.$comuna->nombreCorto()" :descripcion="'Propuestas, agenda y noticias de Rosa Acevedo en '.$comuna->nombre.'.'">
    @if ($banners->isNotEmpty())
        <x-ra.banner :banners="$banners" :corta="true" :h1="false" />
    @endif
    @php $imagen = $pagina?->getFirstMedia('imagen'); @endphp
    @if ($banners->isEmpty())
        <x-ra.cabecera :etiqueta="$comuna->rotulo()" :titulo="$comuna->nombreCorto()"
            :imagen="\App\Support\Medios::url($imagen)" :foco="$imagen ? \App\Support\Medios::foco($imagen, [50, 40]) : null" />
    @endif

    <x-ra.aviso-escucha />

    <section class="ra-seccion ra-fondo-marfil">
        <div class="ra-contenedor ra-dos-columnas">
            <div class="ra-pila-4">
                @if ($banners->isNotEmpty())
                    <span class="ra-etiqueta">{{ $comuna->rotulo() }}</span>
                    <h1 class="ra-h2">{{ $comuna->nombreCorto() }}</h1>
                @endif
                @if ($pagina?->saludo)
                    <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($pagina->saludo) }}</div>
                @else
                    <p class="ra-entradilla">Cuéntanos qué necesita tu barrio. Con tu registro te contamos lo que pasa en {{ $comuna->esCorregimiento() ? 'el corregimiento' : 'tu comuna' }}.</p>
                @endif
                @if ($whatsapp)
                    <a class="ra-btn ra-btn-whatsapp ra-alinear-inicio" href="{{ $whatsapp }}" target="_blank" rel="noopener" data-conversion="whatsapp_clic" data-umami-event="whatsapp_clic"><x-ra.icono nombre="whatsapp" /> Escríbenos por WhatsApp</a>
                @endif
            </div>
            <x-ra.formulario-registro :titulo="'Súmate en '.$comuna->nombreCorto()" :comuna-id="$comuna->id" :pasos="2" id="registro-comuna" boton="Me planto" />
        </div>
    </section>

    @include('partials.bloques-lista', ['bloques' => $pagina?->bloques ?? [], 'anterior' => 'marfil'])

    @if ($destacadas->isNotEmpty())
        <section class="ra-seccion ra-fondo-blanco" aria-labelledby="destacadas">
            <div class="ra-contenedor ra-pila-5">
                <h2 class="ra-h2-medio" id="destacadas">Lo que nos han propuesto aquí</h2>
                <div class="ra-grilla ra-grilla-2">
                    @foreach ($destacadas as $propuesta)
                        <blockquote class="ra-cita ra-cita-tarjeta"><p>{{ \Illuminate\Support\Str::limit($propuesta->texto, 280) }}</p><footer>{{ $propuesta->tema?->nombre }} · {{ \App\Models\PropuestaCiudadana::ESTADOS[$propuesta->estado] }}</footer></blockquote>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('partials.bloques.agenda', ['d' => ['titulo' => 'Encuentros en '.$comuna->nombreCorto()], 'clasesFondo' => 'ra-fondo-arena'])
    @include('partials.bloques.noticias', ['d' => ['titulo' => 'Noticias de '.$comuna->nombreCorto()], 'clasesFondo' => 'ra-fondo-marfil'])

    @if ($barrios->isNotEmpty())
        <section class="ra-seccion-compacta ra-fondo-blanco" aria-labelledby="barrios">
            <div class="ra-contenedor ra-pila">
                <h2 class="ra-h3" id="barrios">{{ $comuna->esCorregimiento() ? 'Sectores y veredas' : 'Barrios' }}</h2>
                <p class="ra-lista-barrios">{{ $barrios->pluck('nombre')->join(' · ') }}</p>
            </div>
        </section>
    @endif

    <section class="ra-seccion ra-fondo-marfil" aria-labelledby="otras">
        <div class="ra-contenedor ra-pila-5">
            <h2 class="ra-h2-medio" id="otras">Otros territorios</h2>
            <x-ra.selector-comuna :comunas="$comunas" :actual="$comuna" :raices="false" />
        </div>
    </section>
</x-layouts.sitio>
