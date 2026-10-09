{{-- Pasos 2 y 3 sin JavaScript. El token del paso viaja en la sesión. --}}
<x-layouts.sitio titulo="Súmate" :no-indexar="true">
    <x-ra.cabecera etiqueta="Súmate" titulo="Ya te sumaste" />
    <section class="ra-seccion ra-fondo-marfil">
        <div class="ra-contenedor"><div class="ra-angosto ra-centrar">
            <form class="ra-form ra-form-tarjeta" method="POST" action="{{ route('sumate.completar') }}" novalidate aria-labelledby="continuar-titulo">
                @csrf
                <div class="ra-pasos" aria-hidden="true">
                    @for ($p = 1; $p <= 3; $p++)
                        <span class="ra-paso {{ $p < $paso ? 'ra-paso-hecho' : ($p === $paso ? 'ra-paso-activo' : '') }}"></span>
                    @endfor
                </div>
                <p class="ra-pequeno ra-sin-margen">Paso {{ $paso }} de 3</p>
                <div class="ra-aviso ra-aviso-exito" role="status"><x-ra.icono nombre="exito" /><span>Ya te sumaste. Tu código es <strong>{{ session('registro.codigo') }}</strong>.</span></div>
                @if ($errors->has('token'))
                    <div class="ra-aviso ra-aviso-error" role="alert"><x-ra.icono nombre="alerta" /><span>{{ $errors->first('token') }}</span></div>
                @endif
                @if ($paso === 2)
                    <h2 class="ra-h3" id="continuar-titulo">Cuéntanos de tu barrio</h2>
                    <x-ra.campo nombre="email" etiqueta="Correo" tipo="email" autocomplete="email" maxlength="160" :opcional="true" />
                    <x-ra.campo nombre="barrio_id" etiqueta="Barrio o vereda" tipo="select" ayuda="Con tu barrio te contamos lo que pasa en tu comuna.">
                        <option value="">Elige tu barrio o vereda</option>
                        @foreach ($barrios as $comuna => $lista)
                            <optgroup label="{{ $comuna }}">
                                @foreach ($lista as $barrio)
                                    <option value="{{ $barrio->id }}" @selected((string) old('barrio_id') === (string) $barrio->id)>{{ $barrio->nombre }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </x-ra.campo>
                    <button type="submit" class="ra-btn ra-btn-principal ra-btn-bloque">Continuar</button>
                @else
                    <h2 class="ra-h3" id="continuar-titulo">¿Quieres ayudar?</h2>
                    @include('partials.campos-voluntariado', ['prefijo' => 'continuar'])
                    <button type="submit" class="ra-btn ra-btn-principal ra-btn-bloque">Quiero ser voluntario</button>
                @endif
                <a class="ra-btn ra-btn-fantasma ra-btn-bloque" href="{{ route('sumate.gracias') }}">Ahora no</a>
            </form>
        </div></div>
    </section>
</x-layouts.sitio>
