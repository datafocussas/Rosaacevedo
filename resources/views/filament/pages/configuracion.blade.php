<x-filament-panels::page>
    <form wire:submit="guardar" class="space-y-6">
        {{ $this->form }}
        <x-filament::button type="submit">Guardar configuración</x-filament::button>
    </form>
</x-filament-panels::page>
