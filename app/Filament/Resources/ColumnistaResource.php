<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\ColumnistaResource\Pages;
use App\Models\Columnista;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/** Autores de las columnas de opinión. Un columnista con columnas no se borra: se desactiva. */
class ColumnistaResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Columnista::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $modelLabel = 'columnista';

    protected static ?string $pluralModelLabel = 'Columnistas';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('nombre')->required()->maxLength(120)->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                Forms\Components\TextInput::make('slug')->label('Identificador')->required()->maxLength(140)->unique(ignoreRecord: true)->alphaDash(),
                Forms\Components\TextInput::make('perfil')->maxLength(160)->columnSpanFull()
                    ->placeholder('Lideresa comunal de San Pío X')->helperText('Una línea que aparece bajo el nombre.'),
                Forms\Components\Textarea::make('biografia')->label('Biografía corta')->rows(3)->maxLength(600)->columnSpanFull()
                    ->helperText('Aparece al final de cada columna. Solo datos que la persona haya confirmado.'),
                SpatieMediaLibraryFileUpload::make('foto')->collection('foto')->label('Foto')->image()->imageEditor()->imageEditorAspectRatios(['1:1'])->maxSize(2048)
                    ->customProperties(fn (Forms\Get $get) => ['alt' => $get('nombre')]),
                Forms\Components\Group::make()->schema([
                    Forms\Components\Toggle::make('activo')->label('Activo')->default(true)
                        ->helperText('Un columnista inactivo no aparece para nuevas columnas; sus columnas publicadas siguen en el sitio.'),
                    Forms\Components\TextInput::make('orden')->numeric()->default(0),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('orden')
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('foto')->collection('foto')->label('')->circular()->height(44),
                Tables\Columns\TextColumn::make('nombre')->searchable()->description(fn (Columnista $r) => $r->perfil),
                Tables\Columns\TextColumn::make('columnas_count')->label('Columnas')->counts('columnas'),
                Tables\Columns\IconColumn::make('activo')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->visible(fn (Columnista $r) => ! $r->columnas()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColumnistas::route('/'),
            'create' => Pages\CreateColumnista::route('/create'),
            'edit' => Pages\EditColumnista::route('/{record}/edit'),
        ];
    }
}
