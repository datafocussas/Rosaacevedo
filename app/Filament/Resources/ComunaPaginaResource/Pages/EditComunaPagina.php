<?php

namespace App\Filament\Resources\ComunaPaginaResource\Pages;

use App\Filament\Resources\ComunaPaginaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComunaPagina extends EditRecord
{
    protected static string $resource = ComunaPaginaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
