<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\RedSocialResource\Pages;
use App\Models\RedSocial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** RF-30: redes administrables. Número de WhatsApp, mensaje y canal están en Configuración. */
class RedSocialResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'sitio.configurar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = RedSocial::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = 'Sitio';

    protected static ?string $modelLabel = 'red social';

    protected static ?string $pluralModelLabel = 'Redes';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre')->required()->maxLength(40),
            Forms\Components\TextInput::make('url')->label('Dirección')->url()->required()->maxLength(255),
            Forms\Components\Select::make('icono')->label('Ícono')->required()->native(false)
                ->options(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'x' => 'X', 'youtube' => 'YouTube', 'whatsapp' => 'WhatsApp', 'enlace' => 'Otro']),
            Forms\Components\Toggle::make('activa')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('orden')->defaultSort('orden')
            ->columns([
                Tables\Columns\TextColumn::make('nombre'),
                Tables\Columns\TextColumn::make('url')->label('Dirección')->limit(50),
                Tables\Columns\ToggleColumn::make('activa'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageRedSocials::route('/')];
    }
}
