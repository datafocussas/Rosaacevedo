<?php

namespace App\Filament\Resources\PoliticaResource\Pages;

use App\Filament\Resources\PoliticaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPoliticas extends ListRecords
{
    protected static string $resource = PoliticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
