<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\EnlaceBioResource\Pages;
use App\Models\EnlaceBio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** RF-17: lista de la página /enlaces (biografía de Instagram y TikTok). */
class EnlaceBioResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = EnlaceBio::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?string $modelLabel = 'enlace';

    protected static ?string $pluralModelLabel = 'Enlaces (biografía)';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('texto')->required()->maxLength(60),
            Forms\Components\TextInput::make('url')->label('Enlace')->required()->maxLength(255),
            Forms\Components\Select::make('icono')->label('Ícono')->native(false)->options([
                'arrow-right' => 'Flecha', 'sprout' => 'Brote', 'whatsapp' => 'WhatsApp', 'calendario' => 'Calendario',
                'facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'x' => 'X', 'youtube' => 'YouTube', 'enlace' => 'Enlace',
            ]),
            Forms\Components\Toggle::make('activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('orden')->defaultSort('orden')
            ->columns([
                Tables\Columns\TextColumn::make('texto'),
                Tables\Columns\TextColumn::make('url')->label('Enlace')->limit(50),
                Tables\Columns\ToggleColumn::make('activo'),
            ])
            ->headerActions([Tables\Actions\Action::make('ver')->label('Ver /enlaces')->url(fn () => route('enlaces'), true)])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageEnlaceBios::route('/')];
    }
}
