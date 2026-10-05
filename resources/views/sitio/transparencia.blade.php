<x-layouts.sitio titulo="Transparencia" descripcion="Financiación de la campaña, gerente de campaña y enlace a Cuentas Claras.">
    <header class="ra-seccion ra-cabecera-pagina">
        <div class="ra-contenedor ra-lectura ra-pila-4">
            <span class="ra-etiqueta">Transparencia</span>
            <h1 class="ra-display">Cómo se financia esta campaña</h1>
        </div>
    </header>
    <section class="ra-seccion-compacta">
        <div class="ra-contenedor ra-prosa">
            <p><strong>Gerente de campaña:</strong> {{ $datos['gerente'] ?? '[POR CONFIRMAR]' }}</p>
            @if (! empty($datos['texto']))<div>{{ \App\Support\Texto::enriquecido($datos['texto']) }}</div>@endif
            <p><a href="{{ $datos['cuentas_claras'] ?? 'https://www.cnecuentasclaras.gov.co/' }}" target="_blank" rel="noopener">Consulta los ingresos y gastos en Cuentas Claras</a> (Consejo Nacional Electoral).</p>
        </div>
    </section>
</x-layouts.sitio>
