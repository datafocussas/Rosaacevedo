{{-- Entrada: banners administrables + formulario de registro (paso 1). --}}
@php
    $banners ??= collect();
    $porDefecto = new \App\Models\Banner([
        'etiqueta' => 'Por el futuro de Itagüí',
        'titular' => 'Raíces que permanecen. Compromisos que cumplimos.',
        'texto' => 'Queremos construir contigo el programa de gobierno. Súmate y recibe las propuestas para tu barrio.',
        'mostrar_lema' => true,
    ]);
    $lista = $banners->isNotEmpty() ? $banners : collect([$porDefecto]);
@endphp
<x-ra.banner :banners="$lista">
    <x-slot:formulario>
        <x-ra.formulario-registro
            :titulo="$d['titulo_formulario'] ?? 'Recibe las propuestas para tu barrio'"
            :etiqueta="$d['etiqueta_formulario'] ?? 'Súmate a la siembra'"
            :pasos="2"
            id="registro-inicio" />
    </x-slot:formulario>
</x-ra.banner>
