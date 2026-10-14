{{-- Entrada de inicio (diseño v2, §6.2): banners + cápsula de registro + rosa. Fondo noche fijo. --}}
@php
    $banners ??= collect();
    $porDefecto = new \App\Models\Banner([
        'etiqueta' => 'Por el futuro de Itagüí',
        'titular' => 'Raíces que permanecen. Compromisos que cumplimos.',
        'mostrar_lema' => true,
    ]);
    $lista = $banners->isNotEmpty() ? $banners : collect([$porDefecto]);
@endphp
<x-ra.banner :banners="$lista">
    <x-slot:formulario>
        <x-ra.formulario-registro variante="capsula" boton="Me planto"
            :titulo="$d['titulo_formulario'] ?? 'Recibe las propuestas para tu barrio'"
            :etiqueta="$d['etiqueta_formulario'] ?? 'Súmate a la siembra'"
            :pasos="3" id="registro-inicio" />
    </x-slot:formulario>
</x-ra.banner>
