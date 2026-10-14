@props(['rosa' => true, 'nivel' => 'p'])
{{-- «Aquí me planto.» en Montserrat 800, sin rotar (diseño v2, §6.3). Fuera de la entrada lleva la rosa al lado.
     Los colores se adaptan solos: marfil y coral sobre oscuro; tinta y coral hondo sobre claro. --}}
@if ($rosa)
<div class="ra-lema-fila">
    <div class="ra-lema-bloque">
        <{{ $nivel }} class="ra-lema">Aquí <span class="ra-lema-acento">me planto.</span></{{ $nivel }}>
    </div>
    <x-ra.rosa-raices :halo="false" :decorativa="true" />
</div>
@else
<{{ $nivel }} class="ra-lema">Aquí <span class="ra-lema-acento">me planto.</span></{{ $nivel }}>
@endif
