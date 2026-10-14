@php $redes ??= \App\Models\RedSocial::query()->where('activa', true)->orderBy('orden')->get(); @endphp
<section class="ra-seccion {{ $clasesFondo }}" aria-label="Redes sociales">
    <div class="ra-contenedor"><x-ra.franja-redes :redes="$redes" /></div>
</section>
