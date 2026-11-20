@props(['config'])
{{-- Asistente virtual (botón flotante + panel de chat). Solo aparece si está encendido en el panel y tiene API key.
     Panel no modal: se cierra con Escape y devuelve el foco al botón. Los mensajes se anuncian (role="log").
     La conversación se conserva al cambiar de página (sessionStorage) y se borra al cerrar la pestaña. --}}
<div class="ra-asistente" x-data="asistente({{ json_encode(['bienvenida' => $config['bienvenida']], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) }})"
    @keydown.escape.window="abierto && cerrar()">
    <button type="button" class="ra-asistente-boton" x-ref="boton" @click="abierto ? cerrar() : abrir()" :aria-expanded="abierto.toString()" aria-expanded="false" aria-controls="ra-asistente-panel">
        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 12a8 8 0 0 1-11.8 7L4 20.5l1.5-4.6A8 8 0 1 1 21 12z"/></svg>
        <span class="ra-asistente-boton-texto">Pregúntale al asistente</span>
    </button>

    <section id="ra-asistente-panel" class="ra-asistente-panel ra-oscuro" role="dialog" aria-modal="false" aria-labelledby="ra-asistente-titulo" x-show="abierto" x-cloak
        x-transition:enter.opacity.duration.200ms>
        <header class="ra-asistente-cabecera">
            <div>
                <h2 class="ra-asistente-titulo" id="ra-asistente-titulo">{{ $config['nombre'] }}</h2>
                <p class="ra-asistente-sub ra-sin-margen">Asistente automático con información de la campaña</p>
            </div>
            <div class="ra-asistente-acciones">
                <button type="button" class="ra-asistente-icono" @click="nueva()" x-show="mensajes.length" aria-label="Empezar una conversación nueva">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                </button>
                <button type="button" class="ra-asistente-icono" @click="cerrar()" aria-label="Cerrar el asistente">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        </header>

        <div class="ra-asistente-mensajes" x-ref="lista" role="log" aria-live="polite" aria-relevant="additions">
            <p class="ra-asistente-msg ra-asistente-msg-asistente" x-text="config.bienvenida"></p>
            <template x-for="(mensaje, i) in mensajes" :key="i">
                <p class="ra-asistente-msg" :class="mensaje.rol === 'usuario' ? 'ra-asistente-msg-usuario' : 'ra-asistente-msg-asistente'">
                    <span class="ra-sr" x-text="mensaje.rol === 'usuario' ? 'Tú: ' : 'Asistente: '"></span><span x-html="formato(mensaje.texto)"></span>
                </p>
            </template>
            <p class="ra-asistente-msg ra-asistente-msg-asistente ra-asistente-escribiendo" x-show="cargando"><span class="ra-sr">El asistente está escribiendo</span><span aria-hidden="true">···</span></p>
        </div>

        @if ($config['sugerencias'])
            <div class="ra-asistente-sugerencias" x-show="!mensajes.length && !cargando" role="group" aria-label="Preguntas sugeridas">
                @foreach ($config['sugerencias'] as $sugerencia)
                    <button type="button" class="ra-asistente-chip" @click="enviar(@js($sugerencia))">{{ $sugerencia }}</button>
                @endforeach
            </div>
        @endif

        <form class="ra-asistente-form" @submit.prevent="enviar()">
            <label for="ra-asistente-entrada" class="ra-sr">Escribe tu pregunta</label>
            <textarea id="ra-asistente-entrada" x-ref="entrada" x-model="entrada" rows="1" maxlength="{{ \App\Services\Asistente\Asistente::MAX_CARACTERES }}"
                placeholder="Escribe tu pregunta" @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); enviar(); }" :disabled="cargando"></textarea>
            <button type="submit" class="ra-asistente-enviar" :disabled="cargando || !entrada.trim()" aria-label="Enviar pregunta">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </form>
        <p class="ra-asistente-error" role="alert" x-show="error" x-text="error"></p>
        <p class="ra-asistente-aviso">Respuestas automáticas: pueden tener errores. No compartas datos personales. Guardamos las conversaciones sin datos de contacto por {{ $config['dias_retencion'] }} días para mejorar el asistente. <a href="{{ route('politica-de-datos') }}">Política de datos</a>.</p>
    </section>
</div>
