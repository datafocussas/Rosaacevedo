@props(['columnista', 'tamano' => 56])
{{-- Foto redonda del columnista; sin foto, sus iniciales sobre abismo. Decorativa: el nombre va siempre en texto al lado. --}}
@php $foto = $columnista->getFirstMedia('foto'); @endphp
<span {{ $attributes->merge(['class' => 'ra-avatar']) }} style="width: {{ $tamano }}px; height: {{ $tamano }}px" aria-hidden="true">
    @if ($foto)
        <img src="{{ \App\Support\Medios::url($foto, 'w400') }}" alt="" width="{{ $tamano }}" height="{{ $tamano }}" loading="lazy" decoding="async">
    @else
        <span>{{ $columnista->iniciales() }}</span>
    @endif
</span>
