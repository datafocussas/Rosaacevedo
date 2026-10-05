<?php

namespace App\Filament\Resources\SolicitudTitularResource\Pages;

use App\Filament\Resources\SolicitudTitularResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudTitulars extends ListRecords
{
    protected static string $resource = SolicitudTitularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
