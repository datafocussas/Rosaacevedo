<x-layouts.sitio titulo="Transparencia" descripcion="Financiación de la campaña, gerente de campaña y enlace a Cuentas Claras.">
    <x-ra.cabecera etiqueta="Transparencia" titulo="Cómo se financia esta campaña" />
    <section class="ra-seccion ra-fondo-marfil">
        <div class="ra-contenedor ra-prosa ra-lectura-centrada">
            <p><strong>Gerente de campaña:</strong> {{ $datos['gerente'] ?? '[POR CONFIRMAR]' }}</p>
            @if (! empty($datos['texto']))<div>{{ \App\Support\Texto::enriquecido($datos['texto']) }}</div>@endif
            <p><a href="{{ $datos['cuentas_claras'] ?? 'https://www.cnecuentasclaras.gov.co/' }}" target="_blank" rel="noopener">Consulta los ingresos y gastos en Cuentas Claras</a> (Consejo Nacional Electoral).</p>
        </div>
    </section>
</x-layouts.sitio>
