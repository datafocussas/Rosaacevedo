<x-layouts.sitio :titulo="$eje->titulo()" :descripcion="$eje->frase">
    <header class="ra-seccion ra-cabecera-pagina">
        <div class="ra-contenedor ra-lectura ra-pila-4">
            <a class="ra-enlace-fuerte" href="{{ route('propuestas') }}">Todas las propuestas</a>
            <div class="ra-eje-cabecera">
                <span class="ra-eje-icono"><x-ra.icono :nombre="$eje->icono" /></span>
                <span class="ra-etiqueta">Aquí me planto</span>
            </div>
            <h1 class="ra-display">{{ $eje->titulo() }}</h1>
            @if ($eje->frase)<p class="ra-cuerpo-lg ra-sin-margen">{{ $eje->frase }}</p>@endif
        </div>
    </header>

    @foreach ($eje->contenido ?? [] as $bloque)
        @include('partials.bloque', ['bloque' => $bloque])
    @endforeach

    @if (! empty($eje->compromisos))
        <section class="ra-seccion" aria-labelledby="compromisos">
            <div class="ra-contenedor ra-lectura">
                <h2 class="ra-h2 ra-titulo-seccion" id="compromisos">Nuestros compromisos</h2>
                <ul class="ra-compromisos">
                    @foreach ($eje->compromisos as $compromiso)
                        <li><x-ra.icono nombre="exito" /><span>{!! \App\Support\Texto::marcarPendientes(e(is_array($compromiso) ? ($compromiso['texto'] ?? '') : $compromiso)) !!}</span></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @elseif (empty($eje->contenido))
        <section class="ra-seccion">
            <div class="ra-contenedor ra-lectura">
                <div class="ra-aviso ra-aviso-alerta" role="note"><x-ra.icono nombre="alerta" /><span>Estamos construyendo este eje con la ciudadanía. Cuéntanos qué propones.</span></div>
            </div>
        </section>
    @endif

    @if ($destacadas->isNotEmpty())
        <section class="ra-seccion ra-fondo-blanco" aria-labelledby="destacadas">
            <div class="ra-contenedor">
                <h2 class="ra-h2 ra-titulo-seccion" id="destacadas">Lo que nos han propuesto</h2>
                <div class="ra-grilla ra-grilla-2">
                    @foreach ($destacadas as $propuesta)
                        <blockquote class="ra-cita">
                            <p>{{ \Illuminate\Support\Str::limit($propuesta->texto, 280) }}</p>
                            <footer>Propuesta ciudadana · {{ $propuesta->barrio?->comuna?->rotulo() ?? 'Itagüí' }} · {{ \App\Models\PropuestaCiudadana::ESTADOS[$propuesta->estado] }}</footer>
                        </blockquote>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($noticias->isNotEmpty())
        <section class="ra-seccion" aria-labelledby="noticias-eje">
            <div class="ra-contenedor">
                <h2 class="ra-h2 ra-titulo-seccion" id="noticias-eje">Noticias de este tema</h2>
                <div class="ra-grilla ra-grilla-3">
                    @foreach ($noticias as $noticia)<x-ra.tarjeta-noticia :noticia="$noticia" />@endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="ra-seccion ra-franja-raiz" aria-labelledby="buzon-eje">
        <div class="ra-contenedor ra-fila-llamado">
            <div class="ra-lectura">
                <span class="ra-etiqueta">Buzón ciudadano</span>
                <h2 class="ra-h2 ra-titulo-seccion-corto" id="buzon-eje">¿Qué propones para {{ $eje->articulo }} {{ $eje->sujeto }}?</h2>
            </div>
            <x-ra.boton :href="route('buzon', array_filter(['tema' => $temaId]))" icono="arrow-right">Deja tu propuesta</x-ra.boton>
        </div>
    </section>

    <div class="ra-contenedor ra-lectura">
        <x-ra.compartir :url="route('propuestas.eje', $eje)" :titulo="'Aquí me planto '.mb_strtolower($eje->titulo()).' · Rosa Acevedo'" />
    </div>

    <section class="ra-seccion" aria-labelledby="otros-ejes">
        <div class="ra-contenedor ra-lectura">
            <h2 class="ra-h3 ra-titulo-seccion" id="otros-ejes">Otros compromisos</h2>
            <x-ra.ejes :ejes="$otros" />
        </div>
    </section>
</x-layouts.sitio>
