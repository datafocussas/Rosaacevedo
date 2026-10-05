{{-- Cifras: solo datos confirmados con su fuente (regla 1). Sin cifras de la Encuesta Itagüí 2026 (regla 2). --}}
@php $items = collect($d['items'] ?? [])->filter(fn ($i) => filled($i['valor'] ?? null)); @endphp
@if ($items->isNotEmpty())
<section class="ra-inverso ra-seccion" aria-label="{{ $d['titulo'] ?? 'Cifras' }}">
    <div class="ra-contenedor ra-grilla ra-grilla-3">
        @foreach ($items as $item)
            <div class="ra-cifra">
                <span class="ra-cifra-valor">{!! \App\Support\Texto::marcarPendientes(e($item['valor'])) !!}</span>
                <span class="ra-cifra-texto">{!! \App\Support\Texto::marcarPendientes(e($item['texto'] ?? '')) !!}</span>
            </div>
        @endforeach
    </div>
</section>
@endif
