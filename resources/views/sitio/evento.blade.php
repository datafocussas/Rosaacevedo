<x-layouts.sitio :titulo="$evento->titulo" :descripcion="\Illuminate\Support\Str::limit(strip_tags((string) $evento->descripcion), 155)" :datos-estructurados="[
    '@context' => 'https://schema.org', '@type' => 'Event', 'name' => $evento->titulo,
    'startDate' => $evento->inicia_en->toIso8601String(), 'endDate' => $evento->termina_en?->toIso8601String(),
    'eventStatus' => $evento->estado === 'cancelado' ? 'https://schema.org/EventCancelled' : 'https://schema.org/EventScheduled',
    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
    'location' => ['@type' => 'Place', 'name' => $evento->lugar ?: 'Itagüí', 'address' => ['@type' => 'PostalAddress', 'streetAddress' => $evento->direccion, 'addressLocality' => 'Itagüí', 'addressRegion' => 'Antioquia', 'addressCountry' => 'CO']],
    'organizer' => ['@type' => 'Organization', 'name' => 'Campaña Rosa Acevedo', 'url' => url('/')],
]">
    @php $imagen = $evento->getFirstMedia('imagen'); @endphp
    <x-ra.cabecera :etiqueta="ucfirst($evento->tipo).($evento->comuna ? ' · '.$evento->comuna->rotulo() : '')" :titulo="$evento->titulo"
        :imagen="\App\Support\Medios::url($imagen)" :foco="$imagen ? \App\Support\Medios::foco($imagen, [50, 40]) : null">
        <x-slot:antes><a class="ra-volver" href="{{ route('agenda') }}">Agenda</a></x-slot:antes>
        @if ($evento->estado === 'cancelado')<span class="ra-cancelado">Este encuentro se canceló</span>@endif
        <p class="ra-bajada">
            <time datetime="{{ $evento->inicia_en->toIso8601String() }}">{{ ucfirst($evento->inicia_en->translatedFormat('l j \d\e F \d\e Y')) }}, {{ $evento->inicia_en->format('g:i') }} {{ $evento->inicia_en->format('a') === 'am' ? 'a. m.' : 'p. m.' }}</time>
        </p>
        @if ($evento->lugar || $evento->direccion)
            <p class="ra-sin-margen ra-evento-lugar ra-texto-secundario"><x-ra.icono nombre="map-pin" tam="18" /> <span>{{ $evento->lugar }}@if ($evento->direccion) · {{ $evento->direccion }}@endif</span></p>
        @endif
    </x-ra.cabecera>
    <section class="ra-seccion ra-fondo-arena">
        <div class="ra-contenedor ra-dos-columnas">
            <div class="ra-pila-4">
                <div class="ra-prosa">{{ \App\Support\Texto::enriquecido($evento->descripcion) }}</div>
                @if ($evento->estado === 'publicado')
                    <a class="ra-enlace-fuerte" href="{{ route('agenda.ics', $evento) }}">Agregar a mi calendario</a>
                @endif
            </div>
            @if ($evento->estado === 'publicado' && $evento->inicia_en->isFuture())
                <form class="ra-form ra-form-tarjeta" id="asistire" method="POST" action="{{ route('agenda.asistencia', $evento) }}" novalidate aria-labelledby="asistire-titulo">
                    @csrf
                    <h2 class="ra-h3" id="asistire-titulo">Asistiré</h2>
                    @if (session('estado'))
                        <div class="ra-aviso ra-aviso-exito" role="status"><x-ra.icono nombre="exito" /><span>{{ session('estado') }}</span></div>
                    @elseif ($lleno)
                        <div class="ra-aviso ra-aviso-alerta" role="note"><x-ra.icono nombre="alerta" /><span>El cupo de este encuentro se llenó.</span></div>
                    @else
                        <x-ra.campo nombre="nombre" etiqueta="Nombre" autocomplete="given-name" maxlength="120" required />
                        <x-ra.campo nombre="celular" etiqueta="Celular" tipo="tel" inputmode="tel" autocomplete="tel-national" maxlength="16" required />
                        <x-ra.consentimiento tipo="autorizacion_general" nombre="general" :obligatorio="true" />
                        <x-ra.consentimiento tipo="whatsapp" nombre="whatsapp" nota="Opcional. Te recordamos el encuentro por WhatsApp." />
                        <x-ra.antispam :evento-id="$evento->id" :comuna-id="$evento->comuna_id" />
                        <button type="submit" class="ra-btn ra-btn-principal ra-btn-bloque" data-umami-event="asistire">Inscribirme</button>
                    @endif
                </form>
            @endif
        </div>
    </section>
</x-layouts.sitio>
