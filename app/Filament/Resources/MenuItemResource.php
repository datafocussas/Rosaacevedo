<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'sitio.configurar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'Sitio';

    protected static ?string $modelLabel = 'ítem de menú';

    protected static ?string $pluralModelLabel = 'Menú';

    protected static ?int $navigationSort = 1;

    public const UBICACIONES = ['principal' => 'Menú principal', 'pie_sitio' => 'Pie: el sitio', 'pie_transparencia' => 'Pie: transparencia'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('ubicacion')->label('Ubicación')->options(self::UBICACIONES)->required()->native(false)
                ->helperText('El menú principal admite máximo 6 ítems; el botón «Súmate» es fijo.'),
            Forms\Components\TextInput::make('texto')->required()->maxLength(60),
            Forms\Components\TextInput::make('url')->label('Enlace')->required()->maxLength(255)->helperText('Ruta interna («/propuestas») o dirección completa.'),
            Forms\Components\Toggle::make('activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup(Tables\Grouping\Group::make('ubicacion')->label('Ubicación')->getTitleFromRecordUsing(fn ($record) => self::UBICACIONES[$record->ubicacion]))
            ->reorderable('orden')->defaultSort('orden')
            ->columns([
                Tables\Columns\TextColumn::make('texto'),
                Tables\Columns\TextColumn::make('url')->label('Enlace'),
                Tables\Columns\ToggleColumn::make('activo'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageMenuItems::route('/')];
    }
}
