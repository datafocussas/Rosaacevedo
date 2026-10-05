<?php

namespace App\Filament\Resources;

use App\Filament\Bloques;
use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\PaginaResource\Pages;
use App\Models\Pagina;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

/** Páginas por bloques (RF-12), incluida la página de inicio. */
class PaginaResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Pagina::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $pluralModelLabel = 'Páginas';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?string $modelLabel = 'página';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(3)->schema([
                Forms\Components\TextInput::make('titulo')->label('Título')->required()->maxLength(160),
                Forms\Components\TextInput::make('slug')->label('Ruta')->required()->maxLength(120)->unique(ignoreRecord: true)
                    ->disabled(fn (?Pagina $record) => $record && in_array($record->slug, ['inicio', 'conoce-a-rosa', 'manifiesto', 'uso-de-ia'], true))
                    ->dehydrated()->helperText('inicio, conoce-a-rosa, manifiesto y uso-de-ia tienen ruta fija.'),
                Forms\Components\Select::make('estado')->options(['borrador' => 'Borrador', 'publicada' => 'Publicada', 'archivada' => 'Archivada'])->required()->default('borrador')->native(false),
            ]),
            Bloques::builder('bloques', conInicio: true)->columnSpanFull(),
            Forms\Components\Section::make('SEO y redes')->collapsed()->columns(2)->schema([
                Forms\Components\TextInput::make('seo_titulo')->label('Título SEO')->maxLength(70),
                Forms\Components\TextInput::make('seo_descripcion')->label('Descripción SEO')->maxLength(160),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Ruta')->formatStateUsing(fn ($state) => $state === 'inicio' ? '/' : '/'.$state),
                Tables\Columns\TextColumn::make('estado')->badge()->color(fn ($state) => match ($state) {
                    'publicada' => 'success', 'borrador' => 'gray', default => 'warning'
                }),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizada')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('vista_previa')->label('Vista previa')->icon('heroicon-o-eye')
                    ->url(fn (Pagina $record) => URL::temporarySignedRoute('vista-previa.pagina', now()->addHours(2), ['pagina' => $record->id]), true),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaginas::route('/'),
            'create' => Pages\CreatePagina::route('/create'),
            'edit' => Pages\EditPagina::route('/{record}/edit'),
        ];
    }
}
