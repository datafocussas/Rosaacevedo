<?php

namespace App\Filament\Resources\EnlaceBioResource\Pages;

use App\Filament\Resources\EnlaceBioResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEnlaceBios extends ManageRecords
{
    protected static string $resource = EnlaceBioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
