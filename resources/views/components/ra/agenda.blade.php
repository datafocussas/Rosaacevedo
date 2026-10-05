@props(['eventos', 'nivel' => 3])
{{-- Próximos encuentros (AgendaEventos). --}}
<div class="ra-agenda">
    @foreach ($eventos as $evento)
        <article class="ra-evento">
            <div class="ra-fecha" aria-hidden="true">
                <span class="ra-fecha-dia">{{ $evento->inicia_en->format('j') }}</span>
                <span class="ra-fecha-mes">{{ $evento->inicia_en->translatedFormat('M') }}</span>
            </div>
            <div class="ra-evento-cuerpo">
                <h{{ $nivel }} class="ra-h3 ra-evento-titulo"><a href="{{ route('agenda.show', $evento) }}">{{ $evento->titulo }}</a></h{{ $nivel }}>
                <span class="ra-pequeno ra-evento-lugar">
                    <x-ra.icono nombre="map-pin" tam="16" />
                    <span>
                        <time datetime="{{ $evento->inicia_en->toIso8601String() }}">{{ $evento->inicia_en->translatedFormat('l j \d\e F') }} · {{ $evento->inicia_en->format('g:i') }} {{ $evento->inicia_en->format('a') === 'am' ? 'a. m.' : 'p. m.' }}</time>
                        @if ($evento->comuna) · {{ $evento->comuna->rotulo() }}@endif
                        @if ($evento->lugar) · {{ $evento->lugar }}@endif
                    </span>
                </span>
                <a class="ra-btn ra-btn-fantasma ra-btn-compacto" href="{{ route('agenda.show', $evento) }}#asistire">Asistiré<span class="ra-sr"> a {{ $evento->titulo }}</span></a>
            </div>
        </article>
    @endforeach
</div>
