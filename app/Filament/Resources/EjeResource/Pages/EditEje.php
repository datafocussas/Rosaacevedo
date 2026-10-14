<?php

namespace App\Filament\Resources\EjeResource\Pages;

use App\Filament\Resources\EjeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEje extends EditRecord
{
    protected static string $resource = EjeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
