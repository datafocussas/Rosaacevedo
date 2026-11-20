@php $resumen = $this->resumen(); @endphp
<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Estado</x-slot>
        <p style="font-size:14px">
            @if ($resumen['disponible'])
                <strong style="color:#0f5c45">Visible en el sitio.</strong> Las personas ven el botón «Pregúntale al asistente».
            @elseif (! $resumen['api_key'])
                <strong style="color:#9a5b00">Falta la API key.</strong> Sin ella el asistente no aparece en el sitio.
            @else
                <strong style="color:#9a5b00">Apagado.</strong> No aparece en el sitio.
            @endif
        </p>
        <div style="margin-top:16px;display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
            <div style="border:1px solid rgba(127,127,127,.3);border-radius:12px;padding:16px">
                <p style="font-size:14px;font-weight:600">Base de conocimiento</p>
                <p style="font-size:14px">Unos <strong>{{ number_format($resumen['tokens'], 0, ',', '.') }}</strong> tokens</p>
                @if ($resumen['aviso'])
                    <p style="font-size:12px;color:#b8321f">Es muy grande: cada respuesta será lenta y costosa. Deja solo lo esencial.</p>
                @endif
            </div>
            <div style="border:1px solid rgba(127,127,127,.3);border-radius:12px;padding:16px">
                <p style="font-size:14px;font-weight:600">Hoy</p>
                <p style="font-size:14px"><strong>{{ $resumen['preguntas_hoy'] }}</strong> de {{ number_format($resumen['max_diario'], 0, ',', '.') }} preguntas</p>
            </div>
            <div style="border:1px solid rgba(127,127,127,.3);border-radius:12px;padding:16px">
                <p style="font-size:14px;font-weight:600">Últimos 30 días</p>
                <p style="font-size:14px"><strong>{{ $resumen['conversaciones_30'] }}</strong> conversaciones</p>
                <p style="font-size:12px;opacity:.75">Tokens: {{ number_format($resumen['tokens_30'], 0, ',', '.') }} de entrada y {{ number_format($resumen['salida_30'], 0, ',', '.') }} de salida. El costo exacto se ve en console.anthropic.com.</p>
            </div>
        </div>
        @if ($this->respuestaPrueba)
            <div style="margin-top:16px;border:1px solid rgba(127,127,127,.3);border-radius:12px;padding:16px">
                <p style="font-size:14px;font-weight:600">Última prueba</p>
                <p style="font-size:14px;margin-top:6px"><em>{{ $this->prueba }}</em></p>
                <p style="font-size:14px;margin-top:6px;white-space:pre-line">{{ $this->respuestaPrueba }}</p>
            </div>
        @endif
    </x-filament::section>

    <form wire:submit="guardar" style="display:grid;gap:24px">
        {{ $this->form }}
        <div>
            <x-filament::button type="submit">Guardar configuración</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
