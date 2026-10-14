@props(['etiqueta' => 'Rosa roja con raíces que brillan bajo la tierra', 'halo' => true, 'decorativa' => false, 'prioridad' => false])
{{-- Rosa «Aquí me planto» (pieza de la campaña, octubre de 2026): rosa roja con tallo, hojas y raíces turquesa
     luminosas. Fondo transparente: se diseñó para fondos oscuros (noche, abismo). Archivo fuente en
     public/img/SCR-20261010-mcyq.jpeg; versiones optimizadas en public/img/marca/. El prop «halo» se conserva por
     compatibilidad: el brillo ya viene en la imagen. --}}
<img {{ $attributes->merge(['class' => 'ra-rosa']) }}
    src="{{ asset('img/marca/rosa-aqui-me-planto-420.webp') }}"
    srcset="{{ asset('img/marca/rosa-aqui-me-planto-420.webp') }} 420w, {{ asset('img/marca/rosa-aqui-me-planto-820.webp') }} 820w"
    sizes="(min-width: 980px) 380px, 260px" width="820" height="1082"
    alt="{{ $decorativa ? '' : $etiqueta }}" @if ($decorativa) aria-hidden="true" @endif
    @if ($prioridad) fetchpriority="high" @else loading="lazy" @endif decoding="async">
