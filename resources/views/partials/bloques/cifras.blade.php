{{-- Cifras (diseño v2, §7): abismo, tarjetas de filo oro (1 a 4). Solo cifras confirmadas con su fuente; nunca de la
     Encuesta Itagüí 2026. --}}
@php $items = collect($d['items'] ?? [])->filter(fn ($i) => filled($i['valor'] ?? null))->take(4); @endphp
@if ($items->isNotEmpty())
<section class="ra-seccion-compacta {{ $clasesFondo }}" aria-label="{{ $d['titulo'] ?? 'Cifras' }}">
    <div class="ra-contenedor ra-cifras ra-cifras-{{ max(2, $items->count()) }}">
        @foreach ($items as $item)
            <div class="ra-cifra" data-aparecer>
                <span class="ra-cifra-valor">{!! \App\Support\Texto::marcarPendientes(e($item['valor'])) !!}</span>
                <span class="ra-cifra-texto">{!! \App\Support\Texto::marcarPendientes(e($item['texto'] ?? '')) !!}</span>
            </div>
        @endforeach
    </div>
</section>
@endif
