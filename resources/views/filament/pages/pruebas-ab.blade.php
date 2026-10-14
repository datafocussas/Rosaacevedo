<x-filament-panels::page>
    <div class="flex items-end gap-4">
        <label class="text-sm font-medium">Desde
            <input type="date" wire:model.live="desde" class="block rounded-lg border-gray-300 text-sm dark:bg-gray-900">
        </label>
        <p class="text-sm text-gray-500">Visitantes: personas a las que se asignó la variante. Registros: ciudadanos distintos que completaron el paso 1.</p>
    </div>
    @php $filas = $this->getFilas(); @endphp
    @if (empty($filas))
        <x-filament::section>No hay variantes activas. Crea banners de inicio con variante A y B para empezar una prueba.</x-filament::section>
    @else
        <x-filament::section>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="py-2">Variante</th><th>Banners</th><th class="text-right">Visitantes</th><th class="text-right">Registros</th>
                        <th class="text-right">Conversión</th><th class="text-right">Paso 2</th><th class="text-right">Clics WhatsApp</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filas as $fila)
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="py-3 font-bold">{{ $fila['variante'] }}</td>
                            <td>{{ $fila['banners'] }}</td>
                            <td class="text-right">{{ number_format($fila['visitantes'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($fila['registros'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold">{{ $fila['tasa'] === null ? '—' : number_format($fila['tasa'], 2, ',', '.').' %' }}</td>
                            <td class="text-right">{{ $fila['paso2'] }}</td>
                            <td class="text-right">{{ $fila['whatsapp'] }}</td>
                            <td class="text-right">
                                @can('contenido.gestionar')
                                    <x-filament::button size="xs" color="gray" wire:click="declararGanadora('{{ $fila['variante'] }}')" wire:confirm="¿Declarar ganadora la variante {{ $fila['variante'] }}? Las demás variantes se archivan.">Declarar ganadora</x-filament::button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>
    @endif
</x-filament-panels::page>
