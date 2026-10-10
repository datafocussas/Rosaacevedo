@php
    $resumen = $this->resumen();
    $ejemplo = $this->ejemplo();
    $etiquetas = ['pendiente' => 'En espera', 'enviado' => 'Enviados', 'error' => 'Con error (se reintenta)'];
@endphp
<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Estado de la sincronización</x-slot>
        <p style="font-size:14px">
            @if ($resumen['activa'])
                <strong style="color:#0f5c45">Activa desde el panel.</strong> Cada minuto se envía lo que está en espera.
            @elseif ($resumen['env'])
                <strong>Activa desde el archivo .env del servidor.</strong> Configúrala aquí para manejarla desde el panel.
            @else
                <strong style="color:#9a5b00">Apagada.</strong> El sitio sigue captando y guarda todo en espera hasta que la actives.
            @endif
        </p>
        <div style="margin-top:16px;display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
            @foreach (['ciudadano' => 'Envíos de registros', 'propuesta' => 'Envíos de propuestas'] as $entidad => $titulo)
                <div style="border:1px solid rgba(127,127,127,.3);border-radius:12px;padding:16px">
                    <p style="font-size:14px;font-weight:600">{{ $titulo }}</p>
                    @foreach ($etiquetas as $estado => $texto)
                        <p style="font-size:14px">{{ $texto }}: <strong>{{ $resumen[$entidad][$estado] ?? 0 }}</strong></p>
                    @endforeach
                </div>
            @endforeach
            <div style="border:1px solid rgba(127,127,127,.3);border-radius:12px;padding:16px">
                <p style="font-size:14px;font-weight:600">Registros (leads)</p>
                @foreach (['sincronizado' => 'Sincronizados', 'pendiente' => 'Pendientes', 'error' => 'Con error'] as $estado => $texto)
                    <p style="font-size:14px">{{ $texto }}: <strong>{{ $resumen['leads'][$estado] ?? 0 }}</strong></p>
                @endforeach
            </div>
        </div>
        <p style="margin-top:12px;font-size:12px;opacity:.75">Cada registro puede generar varios envíos (al crearse, al completar el paso 2 y el 3, al dejar una propuesta). El CRM debe actualizar el mismo contacto usando el uuid o el celular, y puede usar el encabezado X-Idempotencia para no duplicar.</p>
        @if ($resumen['errores']->isNotEmpty())
            <div style="margin-top:16px">
                <p style="font-size:14px;font-weight:600">Últimos errores</p>
                <ul style="margin-top:4px;font-size:14px;display:grid;gap:6px">
                    @foreach ($resumen['errores'] as $fila)
                        <li>{{ $fila->entidad }} #{{ $fila->entidad_id }} · {{ $fila->intentos }} intento(s) · próximo: {{ $fila->proximo_intento?->format('d M g:i a') ?? '—' }}<br><span style="font-size:12px;color:#b8321f">{{ $fila->ultimo_error }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-filament::section>

    <form wire:submit="guardar" style="display:grid;gap:24px">
        {{ $this->form }}
        <x-filament::button type="submit">Guardar conexión</x-filament::button>
    </form>

    @if ($ejemplo)
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">Vista previa de lo que se envía (último registro, con la configuración guardada)</x-slot>
            <pre style="overflow-x:auto;border-radius:10px;background:#04151f;color:#f4eee6;padding:16px;font-size:12px">{{ $ejemplo }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
