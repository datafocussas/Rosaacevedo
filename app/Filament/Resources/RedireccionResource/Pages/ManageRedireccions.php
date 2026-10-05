<?php

namespace App\Filament\Resources\RedireccionResource\Pages;

use App\Filament\Resources\RedireccionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRedireccions extends ManageRecords
{
    protected static string $resource = RedireccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
