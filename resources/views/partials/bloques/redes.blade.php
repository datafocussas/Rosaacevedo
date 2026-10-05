@php $redes ??= \App\Models\RedSocial::query()->where('activa', true)->orderBy('orden')->get(); @endphp
<x-ra.franja-redes :redes="$redes" />
