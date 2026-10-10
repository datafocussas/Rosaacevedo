{{-- Paso 3 (voluntariado). La afinidad política es dato sensible: casilla separada y facultativa.
     El puesto de votación solo si la persona lo comparte (regla 5). --}}
@php
    $intereses = \App\Models\Voluntariado::INTERESES;
    $alpine = $alpine ?? false;
@endphp
<fieldset class="ra-campo ra-fieldset">
    <legend class="ra-leyenda">¿Cómo quieres ayudar?</legend>
    @foreach ($intereses as $valor => $texto)
        <label class="ra-check"><input type="checkbox" name="intereses[]" value="{{ $valor }}" @if ($alpine) x-model="datos.intereses" @endif @checked(in_array($valor, old('intereses', [])))> {{ $texto }}</label>
    @endforeach
</fieldset>
<fieldset class="ra-campo ra-fieldset">
    <legend class="ra-leyenda">¿Cuándo puedes? <span class="ra-pequeno">(opcional)</span></legend>
    @foreach (\App\Models\Voluntariado::DISPONIBILIDAD as $valor => $texto)
        <label class="ra-check"><input type="checkbox" name="disponibilidad[]" value="{{ $valor }}" @if ($alpine) x-model="datos.disponibilidad" @endif> {{ $texto }}</label>
    @endforeach
</fieldset>
<div class="ra-campo">
    <label for="{{ $prefijo }}-puesto">Puesto de votación <span class="ra-pequeno">(opcional)</span></label>
    <input class="ra-input" id="{{ $prefijo }}-puesto" name="puesto_votacion" maxlength="160" aria-describedby="{{ $prefijo }}-puesto-ayuda" @if ($alpine) x-model="datos.puesto_votacion" @endif value="{{ old('puesto_votacion') }}">
    <span class="ra-ayuda" id="{{ $prefijo }}-puesto-ayuda">Solo si quieres compartirlo. Nos ayuda a organizar el acompañamiento el día de elecciones.</span>
</div>
<x-ra.consentimiento tipo="afinidad_politica" nombre="afinidad" nota="Opcional. Es un dato sensible: no estás obligado a darlo." :x-model="$alpine ? 'datos.consent_afinidad' : null" />
