{{-- Banner de cookies (sección 07): aceptar, rechazar y configurar al mismo nivel. Por defecto solo
     técnicas; Umami no usa cookies. La elección se guarda 6 meses. Las incrustaciones de redes y
     los píxeles (fase 2) solo se cargan si su categoría está aceptada. --}}
<div class="ra-cookies" x-data="cookies()" x-show="visible" x-cloak role="region" aria-label="Preferencias de cookies">
    <div class="ra-contenedor ra-cookies-caja">
        <p class="ra-sin-margen">Usamos solo cookies técnicas para que el sitio funcione. Con tu permiso, también las de redes sociales para mostrar publicaciones. <a href="{{ route('politica-de-datos') }}#cookies">Más información</a>.</p>
        <div x-show="configurando" class="ra-pila">
            <label class="ra-check"><input type="checkbox" checked disabled> Técnicas (necesarias)</label>
            <label class="ra-check"><input type="checkbox" x-model="redes"> Redes sociales (publicaciones incrustadas)</label>
            <label class="ra-check"><input type="checkbox" x-model="publicidad"> Publicidad (medición de campañas en redes)</label>
        </div>
        <div class="ra-cookies-acciones">
            <button type="button" class="ra-btn ra-btn-secundario" @click="guardar(true)">Aceptar todas</button>
            <button type="button" class="ra-btn ra-btn-secundario" @click="guardar(false)">Rechazar</button>
            <button type="button" class="ra-btn ra-btn-fantasma" x-show="!configurando" @click="configurando = true">Configurar</button>
            <button type="button" class="ra-btn ra-btn-fantasma" x-show="configurando" @click="guardar(null)">Guardar mi elección</button>
        </div>
    </div>
</div>
