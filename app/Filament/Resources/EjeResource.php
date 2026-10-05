<?php

namespace App\Filament\Resources;

use App\Filament\Bloques;
use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\EjeResource\Pages;
use App\Models\Eje;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Ejes «Aquí me planto por…» (RF-13). */
class EjeResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Eje::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?string $modelLabel = 'eje';

    protected static ?string $pluralModelLabel = 'Ejes y propuestas';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(3)->schema([
                Forms\Components\TextInput::make('articulo')->label('Artículo')->required()->maxLength(20)->helperText('la, las, los, una ciudad que'),
                Forms\Components\TextInput::make('sujeto')->required()->maxLength(60),
                Forms\Components\TextInput::make('slug')->label('Ruta')->prefix('/propuestas/')->required()->maxLength(80)->unique(ignoreRecord: true)->alphaDash(),
                Forms\Components\Select::make('icono')->label('Ícono (Lucide)')->required()->native(false)->options([
                    'heart-pulse' => 'heart-pulse (salud)', 'users' => 'users (familias)', 'graduation-cap' => 'graduation-cap (jóvenes)',
                    'store' => 'store (comerciantes)', 'shield' => 'shield (seguridad)', 'trending-up' => 'trending-up (oportunidades)', 'sprout' => 'sprout (ciudad que avanza)',
                ]),
                Forms\Components\TextInput::make('frase')->maxLength(200)->columnSpan(2),
                Forms\Components\Toggle::make('publicado'),
            ]),
            SpatieMediaLibraryFileUpload::make('imagen')->collection('imagen')->image()->imageEditor()->maxSize(4096),
            Forms\Components\Repeater::make('compromisos')->schema([Forms\Components\Textarea::make('texto')->required()->rows(2)])
                ->reorderableWithButtons()->addActionLabel('Agregar compromiso')->helperText('Textos validados por Estrategia.'),
            Bloques::builder('contenido')->label('Diagnóstico y contenido')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('orden')->defaultSort('orden')
            ->columns([
                Tables\Columns\TextColumn::make('titulo')->label('Eje')->state(fn (Eje $r) => $r->titulo()),
                Tables\Columns\TextColumn::make('frase')->limit(60),
                Tables\Columns\ToggleColumn::make('publicado'),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEjes::route('/'),
            'create' => Pages\CreateEje::route('/create'),
            'edit' => Pages\EditEje::route('/{record}/edit'),
        ];
    }
}
