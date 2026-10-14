<?php

namespace App\Filament\Resources\TemaResource\Pages;

use App\Filament\Resources\TemaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTemas extends ManageRecords
{
    protected static string $resource = TemaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
