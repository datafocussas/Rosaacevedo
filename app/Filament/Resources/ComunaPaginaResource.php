<?php

namespace App\Filament\Resources;

use App\Filament\Bloques;
use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\ComunaPaginaResource\Pages;
use App\Models\ComunaPagina;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Contenido propio de cada territorio (RF-14). El catálogo de barrios es de solo lectura. */
class ComunaPaginaResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = ComunaPagina::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?string $modelLabel = 'página de comuna';

    protected static ?string $pluralModelLabel = 'Comunas';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(3)->schema([
                Forms\Components\Select::make('comuna_id')->label('Territorio')->required()->native(false)->unique(ignoreRecord: true)
                    ->relationship('comuna', 'nombre', fn ($query) => $query->where('division', '2024')),
                Forms\Components\TextInput::make('codigo_whatsapp')->label('Código de WhatsApp')->maxLength(30)->placeholder('C4-SANTAMARIA')
                    ->helperText('Va en el mensaje prellenado para atribuir la conversación.'),
                Forms\Components\Toggle::make('publicada'),
            ]),
            Forms\Components\RichEditor::make('saludo')->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),
            SpatieMediaLibraryFileUpload::make('imagen')->collection('imagen')->image()->imageEditor()->maxSize(4096),
            Bloques::builder('bloques')->label('Propuestas locales y contenido')->columnSpanFull(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comuna.nombre')->label('Territorio'),
                Tables\Columns\TextColumn::make('codigo_whatsapp')->label('Código WhatsApp'),
                Tables\Columns\ToggleColumn::make('publicada'),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')->icon('heroicon-o-eye')->url(fn (ComunaPagina $r) => route('comunas.show', $r->comuna), true),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComunaPaginas::route('/'),
            'create' => Pages\CreateComunaPagina::route('/create'),
            'edit' => Pages\EditComunaPagina::route('/{record}/edit'),
        ];
    }
}
