<?php

namespace App\Filament\Resources\EjeResource\Pages;

use App\Filament\Resources\EjeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEjes extends ListRecords
{
    protected static string $resource = EjeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
