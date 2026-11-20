<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\ConocimientoAsistenteResource\Pages;
use App\Models\ConocimientoAsistente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Base de conocimiento del asistente: fichas de texto con su fuente. Es lo único que el asistente sabe de Rosa.
 * Se pueden escribir a mano o importar desde un PDF, Word o texto (Importar documento).
 */
class ConocimientoAsistenteResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = ConocimientoAsistente::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Asistente';

    protected static ?string $navigationLabel = 'Base de conocimiento';

    protected static ?string $modelLabel = 'ficha';

    protected static ?string $pluralModelLabel = 'Base de conocimiento';

    protected static ?string $slug = 'asistente/conocimiento';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('titulo')->label('Tema')->required()->maxLength(160)->placeholder('Trayectoria en el Concejo de Itagüí'),
                Forms\Components\Select::make('categoria')->options(ConocimientoAsistente::CATEGORIAS)->default('otro')->required()->native(false),
                Forms\Components\Textarea::make('contenido')->required()->rows(16)->columnSpanFull()
                    ->helperText('Escribe en frases completas, como se lo explicarías a alguien. Solo datos confirmados por la campaña. Si un dato falta, escribe [POR CONFIRMAR]: el asistente dirá que aún no está confirmado. Nunca cifras de la Encuesta Itagüí 2026.'),
                Forms\Components\TextInput::make('fuente')->maxLength(255)->columnSpanFull()
                    ->placeholder('Hoja de vida oficial entregada por la campaña, octubre de 2026')
                    ->helperText('De dónde sale la información. El asistente la tiene en cuenta y sirve para revisar después.'),
                Forms\Components\Toggle::make('activo')->label('El asistente usa esta ficha')->default(true),
                Forms\Components\TextInput::make('orden')->numeric()->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('categoria')
            ->columns([
                Tables\Columns\TextColumn::make('titulo')->label('Tema')->searchable()->wrap()
                    ->description(fn (ConocimientoAsistente $r) => Str::limit($r->contenido, 110)),
                Tables\Columns\TextColumn::make('categoria')->badge()->formatStateUsing(fn ($state) => ConocimientoAsistente::CATEGORIAS[$state] ?? $state),
                Tables\Columns\TextColumn::make('largo')->label('Tamaño')->state(fn (ConocimientoAsistente $r) => number_format((int) ceil(mb_strlen($r->contenido) / 4), 0, ',', '.').' tokens'),
                Tables\Columns\ToggleColumn::make('activo')->label('Activa'),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizada')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')->options(ConocimientoAsistente::CATEGORIAS),
                Tables\Filters\TernaryFilter::make('activo')->label('Activa'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConocimiento::route('/'),
            'create' => Pages\CreateConocimiento::route('/create'),
            'edit' => Pages\EditConocimiento::route('/{record}/edit'),
        ];
    }
}
