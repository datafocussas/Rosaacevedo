<?php

namespace App\Filament\Resources\ConocimientoAsistenteResource\Pages;

use App\Filament\Resources\ConocimientoAsistenteResource;
use App\Models\ConocimientoAsistente;
use App\Services\Asistente\ExtraerTexto;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListConocimiento extends ListRecords
{
    protected static string $resource = ConocimientoAsistenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importar')->label('Importar documento')->icon('heroicon-o-arrow-up-tray')->color('gray')
                ->modalDescription('Sube un PDF, Word (.docx), .txt o .md. El texto queda en una ficha nueva, apagada, para que la revises antes de que el asistente la use. El archivo no se guarda.')
                ->form([
                    Forms\Components\FileUpload::make('archivo')->label('Documento')->required()->disk('local')->directory('asistente-importar')
                        ->acceptedFileTypes(ExtraerTexto::TIPOS)->maxSize(10240)->storeFileNamesIn('nombre_original'),
                    Forms\Components\TextInput::make('titulo')->label('Tema')->required()->maxLength(160),
                    Forms\Components\Select::make('categoria')->options(ConocimientoAsistente::CATEGORIAS)->default('otro')->required()->native(false),
                    Forms\Components\TextInput::make('fuente')->maxLength(255)->placeholder('Documento de propuestas aprobado por Estrategia'),
                ])
                ->action(function (array $data) {
                    $ruta = Storage::disk('local')->path($data['archivo']);

                    try {
                        $texto = ExtraerTexto::desde($ruta, $data['nombre_original'] ?? $data['archivo']);
                    } catch (\Throwable $e) {
                        Notification::make()->danger()->title('No se pudo leer el documento')->body(Str::limit($e->getMessage(), 200))->send();

                        return;
                    } finally {
                        Storage::disk('local')->delete($data['archivo']);
                    }

                    $ficha = ConocimientoAsistente::query()->create([
                        'titulo' => $data['titulo'],
                        'categoria' => $data['categoria'],
                        'fuente' => $data['fuente'] ?: ($data['nombre_original'] ?? null),
                        'contenido' => $texto,
                        'activo' => false,
                    ]);

                    Notification::make()->success()->title('Documento importado')
                        ->body('Revisa el texto, corrige lo que haga falta y activa la ficha.')->send();

                    $this->redirect(ConocimientoAsistenteResource::getUrl('edit', ['record' => $ficha]));
                }),
            Actions\CreateAction::make()->label('Nueva ficha'),
        ];
    }
}
