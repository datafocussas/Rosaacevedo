<?php

namespace App\Filament\Resources\SolicitudTitularResource\Pages;

use App\Filament\Resources\SolicitudTitularResource;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudTitular extends EditRecord
{
    protected static string $resource = SolicitudTitularResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['atendida_por'] = auth()->id();

        return $data;
    }
}
