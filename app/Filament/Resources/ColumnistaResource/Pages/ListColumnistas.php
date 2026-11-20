<?php

namespace App\Filament\Resources\ColumnistaResource\Pages;

use App\Filament\Resources\ColumnistaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListColumnistas extends ListRecords
{
    protected static string $resource = ColumnistaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
