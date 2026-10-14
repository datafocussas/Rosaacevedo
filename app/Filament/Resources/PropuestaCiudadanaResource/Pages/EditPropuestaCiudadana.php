<?php

namespace App\Filament\Resources\PropuestaCiudadanaResource\Pages;

use App\Filament\Resources\PropuestaCiudadanaResource;
use Filament\Resources\Pages\EditRecord;

class EditPropuestaCiudadana extends EditRecord
{
    protected static string $resource = PropuestaCiudadanaResource::class;

    protected function afterSave(): void
    {
        PropuestaCiudadanaResource::despuesDeGuardar($this->record);
    }
}
