@php $imagen = $eje->getFirstMedia('imagen'); @endphp
<x-layouts.sitio :titulo="$eje->titulo()" :descripcion="$eje->frase">
    <x-ra.cabecera :titulo="$eje->titulo()" :entradilla="$eje->frase" etiqueta="Aquí me planto"
        :imagen="\App\Support\Medios::url($imagen)" :foco="$imagen ? \App\Support\Medios::foco($imagen, [50, 30]) : null">
        <x-slot:antes>
            <a class="ra-volver" href="{{ route('propuestas') }}">Todas las propuestas</a>
            <span class="ra-eje-insignia"><x-ra.icono :nombre="$eje->icono" /></span>
        </x-slot:antes>
    </x-ra.cabecera>

    @include('partials.bloques-lista', ['bloques' => $eje->contenido ?? [], 'anterior' => 'noche'])

    @if (! empty($eje->compromisos))
        <section class="ra-seccion ra-fondo-marfil" aria-labelledby="compromisos">
            <div class="ra-contenedor ra-pila-5">
                <span class="ra-etiqueta">Compromisos</span>
                <h2 class="ra-h2-medio" id="compromisos">Lo que nos comprometemos a hacer</h2>
                <ul class="ra-compromisos">
                    @foreach ($eje->compromisos as $compromiso)
                        <li data-aparecer><x-ra.icono nombre="exito" /><span>{!! \App\Support\Texto::marcarPendientes(e(is_array($compromiso) ? ($compromiso['texto'] ?? '') : $compromiso)) !!}</span></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @elseif (empty($eje->contenido))
        <section class="ra-seccion-compacta ra-fondo-marfil">
            <div class="ra-contenedor ra-lectura-centrada">
                <div class="ra-aviso ra-aviso-alerta" role="note"><x-ra.icono nombre="alerta" /><span>Estamos construyendo este eje con la ciudadanía. Cuéntanos qué propones.</span></div>
            </div>
        </section>
    @endif

    @if ($destacadas->isNotEmpty())
        <section class="ra-seccion ra-fondo-blanco" aria-labelledby="destacadas">
            <div class="ra-contenedor ra-pila-5">
                <h2 class="ra-h2-medio" id="destacadas">Lo que nos han propuesto</h2>
                <div class="ra-grilla ra-grilla-2">
                    @foreach ($destacadas as $propuesta)
                        <blockquote class="ra-cita ra-cita-tarjeta">
                            <p>{{ \Illuminate\Support\Str::limit($propuesta->texto, 280) }}</p>
                            <footer>Propuesta ciudadana · {{ $propuesta->barrio?->comuna?->rotulo() ?? 'Itagüí' }} · {{ \App\Models\PropuestaCiudadana::ESTADOS[$propuesta->estado] }}</footer>
                        </blockquote>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($noticias->isNotEmpty())
        <section class="ra-seccion ra-fondo-marfil" aria-labelledby="noticias-eje">
            <div class="ra-contenedor ra-pila-5">
                <h2 class="ra-h2-medio" id="noticias-eje">Noticias de este tema</h2>
                <x-ra.noticias :noticias="$noticias" />
            </div>
        </section>
    @endif

    <section class="ra-seccion ra-fondo-esmeralda ra-oscuro" aria-labelledby="buzon-eje">
        <div class="ra-contenedor ra-llamado">
            <div class="ra-llamado-texto">
                <span class="ra-etiqueta">Buzón ciudadano</span>
                <h2 class="ra-h2-medio" id="buzon-eje">¿Qué propones para {{ $eje->articulo }} {{ $eje->sujeto }}?</h2>
            </div>
            <x-ra.boton :href="route('buzon', array_filter(['tema' => $temaId]))" icono="arrow-right">Deja tu propuesta</x-ra.boton>
        </div>
    </section>

    <section class="ra-seccion ra-fondo-marfil" aria-labelledby="otros-ejes">
        <div class="ra-contenedor ra-pila-5">
            <h2 class="ra-h2-medio" id="otros-ejes">Otros compromisos</h2>
            <x-ra.ejes :ejes="$otros" variante="lista" />
            <x-ra.compartir :url="route('propuestas.eje', $eje)" :titulo="'Aquí me planto '.mb_strtolower($eje->titulo()).' · Rosa Acevedo'" />
        </div>
    </section>
</x-layouts.sitio>
