<x-layouts.sitio titulo="Mis datos" descripcion="Consulta, actualiza o retira la autorización de tus datos personales.">
    <section class="ra-seccion">
        <div class="ra-contenedor ra-dos-columnas">
            <div class="ra-pila-4">
                <span class="ra-etiqueta">Derechos del titular</span>
                <h1 class="ra-display">Tus datos son tuyos</h1>
                <p class="ra-cuerpo-lg ra-sin-margen">Puedes consultar qué datos tenemos, corregirlos, pedir que los borremos o retirar tu autorización.</p>
                <p class="ra-sin-margen">Respondemos las consultas en máximo 10 días hábiles y los reclamos en máximo 15 días hábiles (Ley 1581 de 2012). Antes de aplicar una supresión o un retiro verificamos que seas tú.</p>
                <p class="ra-pequeno ra-sin-margen">Lee la <a href="{{ route('politica-de-datos') }}">política de tratamiento de datos</a>.</p>
            </div>
            <form class="ra-form" method="POST" action="{{ route('mis-datos.store') }}" novalidate aria-labelledby="titular-titulo">
                @csrf
                <h2 class="ra-h3" id="titular-titulo">Envía tu solicitud</h2>
                @if (session('estado'))
                    <div class="ra-aviso ra-aviso-exito" role="status"><x-ra.icono nombre="exito" /><span>Radicamos tu solicitud con el número <strong>{{ session('estado.radicado') }}</strong>. Te respondemos a más tardar el {{ session('estado.vence_en') }}.</span></div>
                @endif
                <fieldset class="ra-campo ra-fieldset {{ $errors->has('tipo') ? 'ra-campo-error' : '' }}">
                    <legend class="ra-leyenda">¿Qué quieres hacer?</legend>
                    @foreach ($tipos as $valor => $texto)
                        <label class="ra-check"><input type="radio" name="tipo" value="{{ $valor }}" @checked(old('tipo') === $valor) required> {{ $texto }}</label>
                    @endforeach
                    @error('tipo')<span class="ra-mensaje-error"><x-ra.icono nombre="alerta-circulo" tam="18" /> {{ $message }}</span>@enderror
                </fieldset>
                <x-ra.campo nombre="nombre" etiqueta="Nombre completo" autocomplete="name" maxlength="120" required />
                <x-ra.campo nombre="contacto" etiqueta="Celular o correo con el que te registraste" maxlength="160" required ayuda="Te respondemos por este medio." />
                <x-ra.campo nombre="detalle" etiqueta="Detalle" tipo="textarea" maxlength="3000" :opcional="true" />
                <x-ra.antispam />
                <button type="submit" class="ra-btn ra-btn-principal ra-btn-bloque">Enviar solicitud</button>
            </form>
        </div>
    </section>
</x-layouts.sitio>
