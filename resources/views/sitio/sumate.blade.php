<x-layouts.sitio titulo="Súmate" descripcion="Súmate a la siembra: recibe las propuestas para tu barrio y ayuda a construir el programa de gobierno de Itagüí.">
    <section class="ra-seccion">
        <div class="ra-contenedor ra-dos-columnas">
            <div class="ra-pila-4">
                <span class="ra-etiqueta">Súmate</span>
                <x-ra.lema />
                <h1 class="ra-display">Aquí me planto contigo</h1>
                <p class="ra-cuerpo-lg ra-sin-margen">Déjanos tu nombre y tu celular. Te contamos lo que pasa en tu comuna y te invitamos a los encuentros de tu barrio.</p>
                <p class="ra-pequeno ra-sin-margen">No te pedimos cédula. Puedes consultar, corregir o retirar tus datos cuando quieras en <a href="{{ route('mis-datos') }}">Mis datos</a>.</p>
            </div>
            <x-ra.formulario-registro titulo="Súmate a la siembra" :pasos="3" id="registro-sumate" />
        </div>
    </section>
</x-layouts.sitio>
