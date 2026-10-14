<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\RedireccionResource\Pages;
use App\Models\Redireccion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** RF-45: redirecciones 301 para conservar la autoridad de las URL actuales. */
class RedireccionResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'sistema.administrar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Redireccion::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-right';

    protected static ?string $navigationGroup = 'Sitio';

    protected static ?string $modelLabel = 'redirección';

    protected static ?string $pluralModelLabel = 'Redirecciones';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('desde')->label('URL antigua')->required()->maxLength(255)
                ->helperText('Ruta del sitio actual, por ejemplo /quienes-somos.')
                ->dehydrateStateUsing(fn ($state) => Redireccion::normalizar($state))
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('hacia')->label('URL nueva')->required()->maxLength(255),
            Forms\Components\Select::make('codigo')->label('Tipo')->options([301 => '301 · permanente', 302 => '302 · temporal'])->default(301)->required()->native(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('desde')
            ->columns([
                Tables\Columns\TextColumn::make('desde')->label('URL antigua')->searchable(),
                Tables\Columns\TextColumn::make('hacia')->label('URL nueva')->searchable(),
                Tables\Columns\TextColumn::make('codigo')->label('Tipo'),
                Tables\Columns\TextColumn::make('visitas')->numeric()->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageRedireccions::route('/')];
    }
}
