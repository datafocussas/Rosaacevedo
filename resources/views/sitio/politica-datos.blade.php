<x-layouts.sitio titulo="Política de tratamiento de datos" descripcion="Cómo tratamos tus datos personales (Ley 1581 de 2012).">
    <header class="ra-seccion ra-cabecera-pagina">
        <div class="ra-contenedor ra-lectura ra-pila-4">
            <span class="ra-etiqueta">Transparencia</span>
            <h1 class="ra-display">Política de tratamiento de datos</h1>
            @if ($politica)
                <p class="ra-pequeno ra-sin-margen">Versión {{ $politica->version }}, vigente desde el {{ $politica->vigente_desde->translatedFormat('j \d\e F \d\e Y') }}. Huella SHA-256: <code class="ra-codigo">{{ $politica->hash_sha256 }}</code></p>
            @endif
        </div>
    </header>
    <section class="ra-seccion-compacta">
        <div class="ra-contenedor">
            @if ($politica)
                <div class="ra-prosa">{{ \App\Support\Texto::enriquecido(\Illuminate\Support\Str::markdown($politica->texto)) }}</div>
            @else
                <div class="ra-aviso ra-aviso-alerta" role="note"><x-ra.icono nombre="alerta" /><span>[POR CONFIRMAR] La política de tratamiento está en revisión jurídica.</span></div>
            @endif

            @if ($autorizaciones->isNotEmpty())
                <div class="ra-prosa">
                    <h2>Textos de autorización vigentes</h2>
                    <table class="ra-tabla-simple">
                        <thead><tr><th scope="col">Autorización</th><th scope="col">Texto</th><th scope="col">Versión</th></tr></thead>
                        <tbody>
                            @foreach ($autorizaciones as $tipo => $texto)
                                <tr><td>{{ \App\Models\Politica::TIPOS[$tipo] }}</td><td>{{ $texto->texto }}</td><td>{{ $texto->version }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="ra-prosa" id="cookies">
                <h2>Cookies</h2>
                @if ($cookies)
                    {{ \App\Support\Texto::enriquecido(\Illuminate\Support\Str::markdown($cookies->texto)) }}
                @else
                    <p>Por defecto usamos solo cookies técnicas: sesión, protección contra falsificación de formularios y la variante de la página que ves (prueba A/B de primera parte). La analítica del sitio no usa cookies. Las publicaciones incrustadas de redes sociales y la medición de campañas solo se activan si las aceptas. Tu elección se guarda seis meses.</p>
                @endif
                <p><button type="button" class="ra-btn ra-btn-fantasma" data-abrir-cookies>Cambiar mis preferencias de cookies</button></p>
            </div>

            <div class="ra-prosa">
                <h2>Tus derechos</h2>
                <p>Puedes consultar, actualizar, suprimir tus datos o retirar tu autorización en cualquier momento en <a href="{{ route('mis-datos') }}">Mis datos</a>.</p>
            </div>
        </div>
    </section>
</x-layouts.sitio>
