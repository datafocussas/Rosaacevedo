<?php

namespace App\Filament\Resources\PaginaResource\Pages;

use App\Filament\Resources\PaginaResource;
use App\Models\Pagina;
use App\Services\MenuPaginas;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPagina extends EditRecord
{
    protected static string $resource = PaginaResource::class;

    private ?string $rutaAnterior = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ver')->label('Ver en el sitio')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                ->visible(fn () => $this->record->estado === 'publicada')
                ->url(fn () => url($this->record->ruta()), true),
            Actions\DeleteAction::make()->hidden(fn () => in_array($this->record->slug, Pagina::FIJAS, true)),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $menu = app(MenuPaginas::class);
        $data['menu_ubicaciones'] = $menu->ubicaciones($this->record);
        $data['menu_texto'] = $menu->texto($this->record);

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->rutaAnterior = $this->record->ruta();
    }

    protected function afterSave(): void
    {
        PaginaResource::avisarSiMuyOscura((array) ($this->record->bloques ?? []));
        app(MenuPaginas::class)->sincronizar($this->record, (array) ($this->data['menu_ubicaciones'] ?? []), $this->data['menu_texto'] ?? null, $this->rutaAnterior);
    }
}
