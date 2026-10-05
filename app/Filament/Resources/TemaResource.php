<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\TemaResource\Pages;
use App\Models\Tema;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TemaResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'sitio.configurar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Tema::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Sitio';

    protected static ?string $modelLabel = 'tema del buzón';

    protected static ?string $pluralModelLabel = 'Temas';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')->required()->maxLength(60),
            Forms\Components\Select::make('eje_id')->label('Eje')->relationship('eje', 'sujeto')->native(false),
            Forms\Components\Toggle::make('activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('orden')->defaultSort('orden')
            ->columns([
                Tables\Columns\TextColumn::make('nombre'),
                Tables\Columns\TextColumn::make('eje.sujeto')->label('Eje'),
                Tables\Columns\ToggleColumn::make('activo'),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageTemas::route('/')];
    }
}
