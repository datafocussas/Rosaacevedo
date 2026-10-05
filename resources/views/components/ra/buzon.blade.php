@props(['temas', 'barrios', 'temaId' => null, 'comunaId' => null])
{{-- Buzón ciudadano (BuzonCiudadano, RF-20). Funciona sin JavaScript. --}}
<form class="ra-form" method="POST" action="{{ route('buzon.store') }}" enctype="multipart/form-data" novalidate aria-labelledby="buzon-titulo" x-data="{ enviando: false }" @submit="enviando = true">
    @csrf
    <h2 class="ra-h3" id="buzon-titulo">Cuéntanos tu propuesta</h2>
    @if ($errors->any())
        <div class="ra-aviso ra-aviso-error" role="alert"><x-ra.icono nombre="alerta" /><span>Revisa los campos marcados.</span></div>
    @endif
    <x-ra.campo nombre="tema_id" etiqueta="Tema" tipo="select" required>
        <option value="">Elige un tema</option>
        @foreach ($temas as $tema)
            <option value="{{ $tema->id }}" @selected((string) old('tema_id', $temaId) === (string) $tema->id)>{{ $tema->nombre }}</option>
        @endforeach
    </x-ra.campo>
    <x-ra.campo nombre="barrio_id" etiqueta="Barrio o vereda" tipo="select" :opcional="true">
        <option value="">Elige tu barrio o vereda</option>
        @foreach ($barrios as $comuna => $lista)
            <optgroup label="{{ $comuna }}">
                @foreach ($lista as $barrio)
                    <option value="{{ $barrio->id }}" @selected((string) old('barrio_id') === (string) $barrio->id)>{{ $barrio->nombre }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </x-ra.campo>
    <x-ra.campo nombre="texto" etiqueta="Tu propuesta" tipo="textarea" maxlength="1500" required ayuda="Hasta 1.500 caracteres." />
    <div class="ra-campo {{ $errors->has('foto') ? 'ra-campo-error' : '' }}">
        <label for="buzon-foto" class="ra-leyenda">Foto <span class="ra-pequeno">(opcional, hasta 5 MB)</span></label>
        <input type="file" id="buzon-foto" name="foto" accept="image/jpeg,image/png,image/webp" class="ra-input ra-archivo" @error('foto') aria-invalid="true" aria-describedby="buzon-foto-err" @enderror>
        @error('foto')<span class="ra-mensaje-error" id="buzon-foto-err"><x-ra.icono nombre="alerta-circulo" tam="18" /> {{ $message }}</span>@enderror
    </div>
    <x-ra.campo nombre="nombre" etiqueta="Nombre" autocomplete="given-name" maxlength="120" required />
    <x-ra.campo nombre="celular" etiqueta="Celular" tipo="tel" inputmode="tel" autocomplete="tel-national" maxlength="16" required ayuda="Para contarte qué pasó con tu propuesta." />
    <x-ra.consentimiento tipo="autorizacion_general" nombre="general" :obligatorio="true" />
    <x-ra.consentimiento tipo="publicar_propuesta" nombre="publicar" nota="Opcional." />
    <div class="ra-aviso ra-aviso-alerta" role="note"><x-ra.icono nombre="alerta" /><span>Usamos herramientas de inteligencia artificial para clasificar las propuestas que recibimos. Una persona revisa cada clasificación. <a href="{{ route('uso-de-ia') }}">Cómo usamos la IA</a>.</span></div>
    <x-ra.antispam :comuna-id="$comunaId" />
    <button type="submit" class="ra-btn ra-btn-principal ra-btn-bloque" :disabled="enviando"><span x-text="enviando ? 'Enviando…' : 'Enviar propuesta'">Enviar propuesta</span></button>
</form>
