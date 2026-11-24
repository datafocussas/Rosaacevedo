<?php

namespace App\Filament\Resources\ColumnaResource\Pages;

use App\Filament\Resources\ColumnaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListColumnas extends ListRecords
{
    protected static string $resource = ColumnaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
