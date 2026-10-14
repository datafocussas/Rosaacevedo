<?php

namespace App\Filament\Resources\ComunaPaginaResource\Pages;

use App\Filament\Resources\ComunaPaginaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComunaPaginas extends ListRecords
{
    protected static string $resource = ComunaPaginaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
