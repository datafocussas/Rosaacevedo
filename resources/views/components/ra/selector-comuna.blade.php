@props(['comunas', 'actual' => null])
{{-- Los 8 territorios del Acuerdo 017 de 2024 hacia sus páginas (SelectorComuna). --}}
<div class="ra-comunas">
    @foreach ($comunas as $comuna)
        @php
            $conteo = $comuna->esCorregimiento()
                ? collect([$comuna->sectores_count ? $comuna->sectores_count.' sectores' : null, $comuna->veredas_count ? $comuna->veredas_count.' veredas' : null])->filter()->join(' · ')
                : ($comuna->barrios_count ? $comuna->barrios_count.' barrios' : '');
        @endphp
        <a class="ra-comuna" href="{{ route('comunas.show', $comuna) }}" @if ($actual && $actual->is($comuna)) aria-current="true" @endif>
            <span class="ra-comuna-num">{{ $comuna->rotulo() }}</span>
            <span class="ra-comuna-nombre">{{ $comuna->nombreCorto() }}</span>
            @if ($conteo)<span class="ra-comuna-barrios">{{ $conteo }}</span>@endif
        </a>
    @endforeach
</div>
