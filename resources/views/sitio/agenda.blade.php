<x-layouts.sitio titulo="Agenda" descripcion="Próximos encuentros de Rosa Acevedo en los barrios y veredas de Itagüí.">
    <header class="ra-seccion ra-cabecera-pagina">
        <div class="ra-contenedor ra-pila-4">
            <span class="ra-etiqueta">Agenda</span>
            <h1 class="ra-display">Nos vemos en el barrio</h1>
        </div>
    </header>
    <section class="ra-seccion-compacta">
        <div class="ra-contenedor ra-lectura">
            @if ($proximos->isEmpty())
                <p>Pronto publicaremos los próximos encuentros. <a href="{{ route('sumate') }}">Súmate</a> y te avisamos.</p>
            @else
                <h2 class="ra-sr">Próximos encuentros</h2>
                <x-ra.agenda :eventos="$proximos" />
            @endif
        </div>
    </section>
    @if ($pasados->isNotEmpty())
        <section class="ra-seccion" aria-labelledby="pasados">
            <div class="ra-contenedor ra-lectura">
                <h2 class="ra-h2 ra-titulo-seccion" id="pasados">Encuentros anteriores</h2>
                <ul class="ra-lista-simple">
                    @foreach ($pasados as $evento)
                        <li><a href="{{ route('agenda.show', $evento) }}">{{ $evento->titulo }}</a> <span class="ra-pequeno">· {{ $evento->inicia_en->translatedFormat('j \d\e F') }}</span></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layouts.sitio>
