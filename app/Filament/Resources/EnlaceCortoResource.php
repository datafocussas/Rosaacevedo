<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\EnlaceCortoResource\Pages;
use App\Models\EnlaceCorto;
use App\Support\CodigoQr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/** RF-09: enlaces cortos /q/{codigo} con QR en PNG y SVG, clics y registros por código. */
class EnlaceCortoResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'enlaces.qr';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = EnlaceCorto::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationGroup = 'Ciudadanía';

    protected static ?string $modelLabel = 'enlace corto';

    protected static ?string $pluralModelLabel = 'Enlaces y QR';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('codigo')->label('Código')->required()->maxLength(20)
                ->default(fn () => strtoupper(Str::random(6)))
                ->regex('/^[A-Za-z0-9_-]+$/')->unique(ignoreRecord: true)
                ->helperText('Letras, números y guiones. Queda como rosaacevedo.com/q/CODIGO.'),
            Forms\Components\TextInput::make('destino')->required()->maxLength(255)->default('/sumate')
                ->helperText('Ruta interna («/sumate», «/propuestas/salud») o dirección completa. Se le agregan utm_* automáticamente.'),
            Forms\Components\TextInput::make('pieza')->maxLength(120)->placeholder('Volante C4 octubre, valla autopista…'),
            Forms\Components\Select::make('comuna_id')->label('Comuna')->native(false)
                ->relationship('comuna', 'nombre', fn ($query) => $query->where('division', '2024')),
            Forms\Components\Select::make('evento_id')->label('Evento')->relationship('evento', 'titulo')->searchable()->preload(),
            Forms\Components\Toggle::make('activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount([
                'interacciones as registros_count' => fn ($q) => $q->where('tipo', 'registro_paso_1'),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('codigo')->label('Código')->searchable()->copyable()->copyableState(fn ($record) => $record->url()),
                Tables\Columns\TextColumn::make('pieza')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('destino')->limit(30),
                Tables\Columns\TextColumn::make('comuna.nombre')->label('Comuna')->limit(20),
                Tables\Columns\TextColumn::make('clics')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('registros_count')->label('Registros')->numeric()->sortable(),
                Tables\Columns\ToggleColumn::make('activo'),
            ])
            ->actions([
                Tables\Actions\Action::make('png')->label('QR PNG')->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (EnlaceCorto $record) => response()->streamDownload(fn () => print (CodigoQr::png($record->url())), 'qr-'.$record->codigo.'.png', ['Content-Type' => 'image/png'])),
                Tables\Actions\Action::make('svg')->label('QR SVG')->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (EnlaceCorto $record) => response()->streamDownload(fn () => print (CodigoQr::svg($record->url())), 'qr-'.$record->codigo.'.svg', ['Content-Type' => 'image/svg+xml'])),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageEnlaceCortos::route('/')];
    }
}
