<x-layouts.sitio titulo="Agenda" descripcion="Próximos encuentros de Rosa Acevedo en los barrios y veredas de Itagüí.">
    <x-ra.cabecera etiqueta="Agenda" titulo="Nos vemos en el barrio" />
    <section class="ra-seccion ra-fondo-arena">
        <div class="ra-contenedor ra-pila-5">
            @if ($proximos->isEmpty())
                <p class="ra-entradilla">No hay encuentros programados. Síguenos para enterarte.</p>
                @php $redes = \App\Models\RedSocial::query()->where('activa', true)->orderBy('orden')->get(); @endphp
                <div class="ra-acciones">
                    @foreach ($redes as $red)
                        <a class="ra-btn ra-btn-fantasma ra-btn-compacto" href="{{ $red->url }}" target="_blank" rel="noopener"><x-ra.icono :nombre="$red->icono" /> {{ $red->nombre }}</a>
                    @endforeach
                </div>
            @else
                <h2 class="ra-sr">Próximos encuentros</h2>
                <x-ra.agenda :eventos="$proximos" />
            @endif
        </div>
    </section>
    @if ($pasados->isNotEmpty())
        <section class="ra-seccion ra-fondo-marfil" aria-labelledby="pasados">
            <div class="ra-contenedor ra-pila-5">
                <h2 class="ra-h2-medio" id="pasados">Encuentros anteriores</h2>
                <ul class="ra-lista-simple">
                    @foreach ($pasados as $evento)
                        <li><a href="{{ route('agenda.show', $evento) }}">{{ $evento->titulo }}</a> <span class="ra-pequeno">· {{ $evento->inicia_en->translatedFormat('j \d\e F') }}</span></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</x-layouts.sitio>
