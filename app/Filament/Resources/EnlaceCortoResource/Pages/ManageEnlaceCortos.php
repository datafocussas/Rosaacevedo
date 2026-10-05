<?php

namespace App\Filament\Resources\EnlaceCortoResource\Pages;

use App\Filament\Resources\EnlaceCortoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEnlaceCortos extends ManageRecords
{
    protected static string $resource = EnlaceCortoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
