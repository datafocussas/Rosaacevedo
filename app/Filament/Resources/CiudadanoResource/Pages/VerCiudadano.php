<?php

namespace App\Filament\Resources\CiudadanoResource\Pages;

use App\Filament\Resources\CiudadanoResource;
use Filament\Resources\Pages\ViewRecord;

class VerCiudadano extends ViewRecord
{
    protected static string $resource = CiudadanoResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // RF-42: quién vio qué ficha y cuándo.
        activity('registros')->causedBy(auth()->user())->performedOn($this->record)
            ->withProperties(['completo' => CiudadanoResource::verCompleto(), 'ip' => request()->ip()])
            ->log('Vio la ficha del ciudadano');
    }
}
