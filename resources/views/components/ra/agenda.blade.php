@props(['eventos', 'nivel' => 3])
{{-- Agenda (diseño v2, §6.9): tarjetas blancas de radio 28 en rejilla de 3; día grande en esmeralda y mes en rótulo oro.
     Eventos cancelados: rótulo «CANCELADO» y título tachado. --}}
<div class="ra-agenda">
    @foreach ($eventos as $evento)
        @php $cancelado = $evento->estado === 'cancelado'; @endphp
        <article class="ra-evento {{ $cancelado ? 'ra-evento-cancelado' : '' }}" data-aparecer>
            <p class="ra-fecha ra-sin-margen">
                <span class="ra-fecha-dia">{{ $evento->inicia_en->format('j') }}</span>
                <span class="ra-fecha-mes">{{ $evento->inicia_en->translatedFormat('M') }}</span>
            </p>
            @if ($cancelado)<span class="ra-cancelado">Cancelado</span>@endif
            <h{{ $nivel }} class="ra-evento-titulo"><a href="{{ route('agenda.show', $evento) }}">{{ $evento->titulo }}</a></h{{ $nivel }}>
            <p class="ra-evento-lugar ra-sin-margen">
                <x-ra.icono nombre="map-pin" tam="16" />
                <span>
                    <time datetime="{{ $evento->inicia_en->toIso8601String() }}">{{ ucfirst($evento->inicia_en->translatedFormat('l j \d\e F')) }} · {{ $evento->inicia_en->format('g:i') }} {{ $evento->inicia_en->format('a') === 'am' ? 'a. m.' : 'p. m.' }}</time>
                    @if ($evento->comuna) · {{ $evento->comuna->nombrePublico() }}@endif
                    @if ($evento->lugar) · {{ $evento->lugar }}@endif
                </span>
            </p>
            @unless ($cancelado)
                <a class="ra-btn ra-btn-fantasma ra-btn-compacto" href="{{ route('agenda.show', $evento) }}#asistire">Asistiré<span class="ra-sr"> a {{ $evento->titulo }}</span></a>
            @endunless
        </article>
    @endforeach
</div>
