@props([
    'titulo' => 'Súmate a la siembra',
    'etiqueta' => null,
    'boton' => 'Súmate',
    'comunaId' => null,
    'eventoId' => null,
    'pasos' => 3,
    'id' => 'registro',
    'variante' => 'claro',
])
{{-- Captación progresiva (FormularioRegistro, RF-01). Sin JavaScript envía el paso 1 por HTML clásico;
     con Alpine hace los pasos 2 y 3 en el mismo lugar con el token firmado guardado en localStorage.
     Diseño v2 (§6.13): «claro» (tarjeta blanca) o «capsula» (vidrio sobre noche; en escritorio, nombre, celular y
     botón en fila con etiquetas ocultas visualmente pero presentes). La lógica es la misma en ambas. --}}
<form id="{{ $id }}" class="ra-form ra-en-paso-1 {{ $variante === 'capsula' ? 'ra-form-capsula ra-capsula' : 'ra-form-tarjeta' }}"
    :class="{ 'ra-en-paso-1': paso === 1 && !terminado }" method="POST" action="{{ route('sumate.store') }}" novalidate aria-labelledby="{{ $id }}-titulo"
    x-data="registro({ pasos: {{ (int) $pasos }}, api: '{{ url('/api/v1') }}', comuna: {{ $comunaId ? (int) $comunaId : 'null' }} })"
    @submit.prevent="enviar($event)" x-ref="form">
    @csrf
    @if ($pasos > 1)
        <div class="ra-pasos" aria-hidden="true">
            @for ($p = 1; $p <= $pasos; $p++)
                <span class="ra-paso {{ $p === 1 ? 'ra-paso-activo' : '' }}" :class="{ 'ra-paso-activo': paso === {{ $p }}, 'ra-paso-hecho': paso > {{ $p }} }"></span>
            @endfor
        </div>
        <p class="ra-pequeno ra-sin-margen ra-paso-texto" x-show="paso <= {{ $pasos }}">Paso <span x-text="paso">1</span> de {{ $pasos }}</p>
    @endif
    @if ($etiqueta)<span class="ra-etiqueta">{{ $etiqueta }}</span>@endif
    <h2 class="ra-h3 ra-form-titulo" id="{{ $id }}-titulo" tabindex="-1" x-ref="titulo" x-text="titulos[paso] ?? @js($titulo)">{{ $titulo }}</h2>

    <div class="ra-aviso ra-aviso-error" role="alert" x-show="errorGeneral" x-cloak>
        <x-ra.icono nombre="alerta" /><span x-text="errorGeneral"></span>
    </div>

    {{-- Paso 1 --}}
    <div class="ra-form-paso ra-form-paso-1" x-show="paso === 1">
        <x-ra.campo clase="ra-campo-nombre" nombre="nombre" etiqueta="Nombre" placeholder="Tu nombre" autocomplete="given-name" maxlength="120" required x-bind:aria-invalid="!!errores.nombre" />
        <template x-if="errores.nombre"><span class="ra-mensaje-error"><x-ra.icono nombre="alerta-circulo" tam="18" /> <span x-text="errores.nombre"></span></span></template>
        <x-ra.campo clase="ra-campo-celular" nombre="celular" etiqueta="Celular" placeholder="Tu celular" tipo="tel" inputmode="tel" autocomplete="tel-national" maxlength="16" required ayuda="10 dígitos, empieza por 3." x-bind:aria-invalid="!!errores.celular" />
        <template x-if="errores.celular"><span class="ra-mensaje-error"><x-ra.icono nombre="alerta-circulo" tam="18" /> <span x-text="errores.celular"></span></span></template>
        <x-ra.consentimiento tipo="autorizacion_general" nombre="general" :obligatorio="true" />
        <template x-if="errores['consent.general']"><span class="ra-mensaje-error"><x-ra.icono nombre="alerta-circulo" tam="18" /> <span x-text="errores['consent.general']"></span></span></template>
        <x-ra.consentimiento tipo="whatsapp" nombre="whatsapp" nota="Opcional. No te agregamos a grupos sin tu permiso. Puedes retirarte cuando quieras." />
        <x-ra.antispam :comuna-id="$comunaId" :evento-id="$eventoId" />
    </div>

    {{-- Paso 2 (solo con JavaScript; sin él, /sumate/continuar) --}}
    <template x-if="paso === 2">
        <div class="ra-form-paso">
            <div class="ra-campo">
                <label for="{{ $id }}-correo">Correo <span class="ra-pequeno">(opcional)</span></label>
                <input class="ra-input" id="{{ $id }}-correo" type="email" autocomplete="email" maxlength="160" x-model="datos.email" :aria-invalid="!!errores.email">
                <template x-if="errores.email"><span class="ra-mensaje-error"><x-ra.icono nombre="alerta-circulo" tam="18" /> <span x-text="errores.email"></span></span></template>
            </div>
            <div class="ra-campo" x-show="barrios.length">
                <label for="{{ $id }}-barrio">Barrio o vereda</label>
                <select class="ra-select" id="{{ $id }}-barrio" x-model="datos.barrio_id" aria-describedby="{{ $id }}-barrio-ayuda" :aria-invalid="!!errores.barrio_id">
                    <option value="">Elige tu barrio o vereda</option>
                    <template x-for="grupo in barrios" :key="grupo.comuna">
                        <optgroup :label="grupo.comuna">
                            <template x-for="b in grupo.items" :key="b.id"><option :value="b.id" x-text="b.nombre"></option></template>
                        </optgroup>
                    </template>
                </select>
                <span class="ra-ayuda" id="{{ $id }}-barrio-ayuda">Con tu barrio te contamos lo que pasa en tu comuna.</span>
                <template x-if="errores.barrio_id"><span class="ra-mensaje-error"><x-ra.icono nombre="alerta-circulo" tam="18" /> <span x-text="errores.barrio_id"></span></span></template>
            </div>
        </div>
    </template>

    {{-- Paso 3: voluntariado --}}
    <template x-if="paso === 3">
        <div class="ra-form-paso">
            @include('partials.campos-voluntariado', ['prefijo' => $id, 'alpine' => true])
        </div>
    </template>

    {{-- Gracias (RF-06) --}}
    <template x-if="paso > {{ $pasos }} || terminado">
        <div class="ra-form-paso" role="status">
            <div class="ra-aviso ra-aviso-exito"><x-ra.icono nombre="exito" /><span><strong>Gracias por sumarte.</strong> Tu código es <strong x-text="codigo"></strong>.</span></div>
            <template x-if="whatsapp">
                <a class="ra-btn ra-btn-whatsapp ra-btn-bloque" :href="whatsapp" target="_blank" rel="noopener" @click="conversion('whatsapp_clic')"><x-ra.icono nombre="whatsapp" /> Escríbenos por WhatsApp</a>
            </template>
        </div>
    </template>

    <div class="ra-form-acciones" x-show="!terminado && paso <= {{ $pasos }}">
        <button type="submit" class="ra-btn ra-btn-principal ra-btn-bloque" :disabled="enviando" :aria-disabled="enviando.toString()">
            <span x-text="enviando ? 'Enviando…' : (botones[paso] ?? @js($boton))">{{ $boton }}</span>
        </button>
        <button type="button" class="ra-btn ra-btn-fantasma ra-btn-bloque" x-show="paso > 1" x-cloak @click="omitir()">Ahora no</button>
    </div>
</form>
