<?php

namespace App\Filament\Resources\PaginaResource\Pages;

use App\Filament\Resources\PaginaResource;
use App\Services\MenuPaginas;
use Filament\Resources\Pages\CreateRecord;

class CreatePagina extends CreateRecord
{
    protected static string $resource = PaginaResource::class;

    protected function afterCreate(): void
    {
        app(MenuPaginas::class)->sincronizar($this->record, (array) ($this->data['menu_ubicaciones'] ?? []), $this->data['menu_texto'] ?? null);
    }
}
