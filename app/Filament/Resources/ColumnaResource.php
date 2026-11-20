<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\ColumnaResource\Pages;
use App\Models\Columna;
use App\Models\Columnista;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/** Columnas de opinión: columnista, editor enriquecido, imagen, programación, borradores y vista previa (como las noticias). */
class ColumnaResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Columna::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $modelLabel = 'columna';

    protected static ?string $pluralModelLabel = 'Columnas de opinión';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columnSpan(2)->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('titulo')->label('Título')->required()->maxLength(120)->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                    Forms\Components\TextInput::make('slug')->label('Ruta')->prefix('/opinion/')->required()->maxLength(160)->unique(ignoreRecord: true)->alphaDash(),
                    Forms\Components\Textarea::make('resumen')->maxLength(300)->rows(2)->helperText('Aparece en la lista de columnas y al compartir.'),
                    Forms\Components\RichEditor::make('cuerpo')->required()
                        ->fileAttachmentsDisk('public')->fileAttachmentsDirectory('opinion')
                        ->toolbarButtons(['attachFiles', 'blockquote', 'bold', 'bulletList', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'undo'])
                        ->helperText('Solo datos y cifras que la campaña haya confirmado. Nunca cifras de la Encuesta Itagüí 2026. Donde falte un dato, [POR CONFIRMAR].'),
                ]),
                Forms\Components\Section::make('Imagen')->schema([
                    SpatieMediaLibraryFileUpload::make('destacada')->collection('destacada')->label('Imagen de la columna (opcional)')->image()->imageEditor()->maxSize(4096)
                        ->customProperties(fn (Forms\Get $get) => ['alt' => $get('titulo')])
                        ->helperText('Si no subes una, la columna muestra la foto del columnista.'),
                ]),
                Forms\Components\Section::make('SEO y redes')->collapsed()->schema([
                    Forms\Components\TextInput::make('seo_titulo')->label('Título SEO')->maxLength(70),
                    Forms\Components\TextInput::make('seo_descripcion')->label('Descripción SEO')->maxLength(160),
                ]),
            ]),
            Forms\Components\Section::make('Publicación')->columnSpan(1)->schema([
                Forms\Components\Select::make('columnista_id')->label('Columnista')->required()->native(false)->searchable()->preload()
                    ->relationship('columnista', 'nombre', fn ($query, string $operation) => $operation === 'create' ? $query->where('activo', true) : $query)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nombre')->required()->maxLength(120),
                        Forms\Components\TextInput::make('perfil')->maxLength(160),
                    ])
                    ->createOptionUsing(fn (array $data) => Columnista::query()->create($data + ['slug' => Str::slug($data['nombre']).'-'.Str::lower(Str::random(4))])->getKey()),
                Forms\Components\Select::make('estado')->options(NoticiaResource::ESTADOS)->default('borrador')->required()->native(false),
                Forms\Components\DateTimePicker::make('publicada_en')->label('Fecha de publicación')->seconds(false)->native(false)
                    ->required(fn (Forms\Get $get) => in_array($get('estado'), ['programada', 'publicada'], true))
                    ->helperText('Si es futura y el estado es «Programada», se publica sola a esa hora.'),
            ]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('publicada_en', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('titulo')->label('Título')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('columnista.nombre')->label('Columnista')->searchable(),
                Tables\Columns\TextColumn::make('estado')->badge()->formatStateUsing(fn ($state) => NoticiaResource::ESTADOS[$state])
                    ->color(fn ($state) => match ($state) {
                        'publicada' => 'success', 'programada' => 'warning', 'revision' => 'info', default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('publicada_en')->label('Publicación')->dateTime('d M Y g:i a')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options(NoticiaResource::ESTADOS),
                Tables\Filters\SelectFilter::make('columnista_id')->label('Columnista')->relationship('columnista', 'nombre'),
            ])
            ->actions([
                Tables\Actions\Action::make('vista_previa')->label('Vista previa')->icon('heroicon-o-eye')
                    ->url(fn (Columna $record) => URL::temporarySignedRoute('vista-previa.columna', now()->addHours(2), ['columna' => $record->id]), true),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColumnas::route('/'),
            'create' => Pages\CreateColumna::route('/create'),
            'edit' => Pages\EditColumna::route('/{record}/edit'),
        ];
    }
}
