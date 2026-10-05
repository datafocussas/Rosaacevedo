<?php

namespace App\Filament\Resources\CiudadanoResource\Pages;

use App\Filament\Resources\CiudadanoResource;
use App\Models\TerritorioComuna;
use App\Services\ExportarRegistros;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;

class ListCiudadanos extends ListRecords
{
    protected static string $resource = CiudadanoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // RF-43: solo Analista (y Administrador), filtrada, con motivo obligatorio y marca de agua.
            Actions\Action::make('exportar')->label('Exportar')->icon('heroicon-o-arrow-down-tray')
                ->visible(fn () => auth()->user()->can('registros.exportar'))
                ->form([
                    Forms\Components\Textarea::make('motivo')->label('Motivo de la exportación')->required()->minLength(15)->maxLength(500)
                        ->helperText('Queda en la bitácora y en el archivo.'),
                    Forms\Components\Select::make('comuna_id')->label('Comuna')->options(fn () => TerritorioComuna::query()->where('division', '2024')->pluck('nombre', 'id'))->native(false),
                    Forms\Components\Select::make('paso')->label('Paso mínimo')->options([1 => 'Paso 1', 2 => 'Paso 2', 3 => 'Paso 3'])->native(false),
                    Forms\Components\Toggle::make('solo_whatsapp')->label('Solo con autorización de WhatsApp vigente'),
                    Forms\Components\DatePicker::make('desde')->label('Registrados desde')->native(false),
                    Forms\Components\DatePicker::make('hasta')->label('Registrados hasta')->native(false),
                ])
                ->action(fn (array $data) => app(ExportarRegistros::class)->descargar(auth()->user(), $data)),
        ];
    }
}
