<x-layouts.sitio titulo="Súmate" descripcion="Súmate a la siembra: recibe las propuestas para tu barrio y ayuda a construir el programa de gobierno de Itagüí.">
    <x-ra.cabecera etiqueta="Súmate" titulo="Aquí me planto contigo"
        entradilla="Déjanos tu nombre y tu celular. Te contamos lo que pasa en tu comuna y te invitamos a los encuentros de tu barrio.">
        <x-slot:antes><x-ra.lema /></x-slot:antes>
    </x-ra.cabecera>
    <section class="ra-seccion ra-fondo-marfil">
        <div class="ra-contenedor ra-dos-columnas">
            <div class="ra-pila-4">
                <span class="ra-etiqueta">Sin letra pequeña</span>
                <h2 class="ra-h2-medio">Tus datos, con cuidado</h2>
                <p class="ra-entradilla">No te pedimos cédula. Puedes consultar, corregir o retirar tus datos cuando quieras en <a href="{{ route('mis-datos') }}">Mis datos</a>.</p>
            </div>
            <x-ra.formulario-registro titulo="Súmate a la siembra" :pasos="3" id="registro-sumate" boton="Me planto" />
        </div>
    </section>
</x-layouts.sitio>
