<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\NoticiaResource\Pages;
use App\Models\Noticia;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/** Noticias (RF-11): editor enriquecido, destacada, galería, video, eje, comunas, programación, borradores y vista previa. */
class NoticiaResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Noticia::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $pluralModelLabel = 'Noticias';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 2;

    public const ESTADOS = ['borrador' => 'Borrador', 'revision' => 'En revisión', 'programada' => 'Programada', 'publicada' => 'Publicada', 'archivada' => 'Archivada'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columnSpan(2)->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('titulo')->label('Título')->required()->maxLength(90)->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                    Forms\Components\TextInput::make('slug')->label('Ruta')->prefix('/noticias/')->required()->maxLength(160)->unique(ignoreRecord: true)->alphaDash(),
                    Forms\Components\Textarea::make('resumen')->maxLength(300)->rows(2),
                    Forms\Components\RichEditor::make('cuerpo')->required()
                        ->fileAttachmentsDisk('public')->fileAttachmentsDirectory('noticias')
                        ->toolbarButtons(['attachFiles', 'blockquote', 'bold', 'bulletList', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'undo']),
                    Forms\Components\TextInput::make('video_url')->label('Video (YouTube o Vimeo)')->url()->maxLength(255),
                ]),
                Forms\Components\Section::make('Imágenes')->schema([
                    SpatieMediaLibraryFileUpload::make('destacada')->collection('destacada')->label('Imagen destacada')->image()->imageEditor()->maxSize(4096)
                        ->customProperties(fn (Forms\Get $get) => ['alt' => $get('alt_destacada')]),
                    Forms\Components\TextInput::make('alt_destacada')->label('Texto alternativo de la imagen destacada')->maxLength(200)->dehydrated(false)
                        ->afterStateHydrated(fn ($component, ?Noticia $record) => $component->state($record?->getFirstMedia('destacada')?->getCustomProperty('alt'))),
                    SpatieMediaLibraryFileUpload::make('galeria')->collection('galeria')->label('Galería')->image()->multiple()->reorderable()->maxSize(4096)
                        ->customProperties(fn (Forms\Get $get) => ['alt' => $get('titulo')]),
                ]),
                Forms\Components\Section::make('SEO y redes')->collapsed()->schema([
                    Forms\Components\TextInput::make('seo_titulo')->label('Título SEO')->maxLength(70),
                    Forms\Components\TextInput::make('seo_descripcion')->label('Descripción SEO')->maxLength(160),
                ]),
            ]),
            Forms\Components\Section::make('Publicación')->columnSpan(1)->schema([
                Forms\Components\Select::make('estado')->options(self::ESTADOS)->default('borrador')->required()->native(false),
                Forms\Components\DateTimePicker::make('publicada_en')->label('Fecha de publicación')->seconds(false)->native(false)
                    ->required(fn (Forms\Get $get) => in_array($get('estado'), ['programada', 'publicada'], true))
                    ->helperText('Si es futura y el estado es «Programada», se publica sola a esa hora.'),
                Forms\Components\Select::make('eje_id')->label('Categoría (eje)')->relationship('eje', 'sujeto')->native(false),
                Forms\Components\Select::make('comunas')->label('Comunas')->multiple()->preload()
                    ->relationship('comunas', 'nombre', fn ($query) => $query->where('division', '2024')),
                Forms\Components\Select::make('autor_id')->label('Autor')->relationship('autor', 'name')->default(fn () => auth()->id())->native(false),
            ]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('publicada_en', 'desc')
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('destacada')->collection('destacada')->label('')->height(54)->width(96),
                Tables\Columns\TextColumn::make('titulo')->label('Título')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('eje.sujeto')->label('Eje')->badge(),
                Tables\Columns\TextColumn::make('estado')->badge()->formatStateUsing(fn ($state) => self::ESTADOS[$state])
                    ->color(fn ($state) => match ($state) {
                        'publicada' => 'success', 'programada' => 'warning', 'revision' => 'info', default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('publicada_en')->label('Publicación')->dateTime('d M Y g:i a')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options(self::ESTADOS),
                Tables\Filters\SelectFilter::make('eje_id')->label('Eje')->relationship('eje', 'sujeto'),
            ])
            ->actions([
                Tables\Actions\Action::make('vista_previa')->label('Vista previa')->icon('heroicon-o-eye')
                    ->url(fn (Noticia $record) => URL::temporarySignedRoute('vista-previa.noticia', now()->addHours(2), ['noticia' => $record->id]), true),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNoticias::route('/'),
            'create' => Pages\CreateNoticia::route('/create'),
            'edit' => Pages\EditNoticia::route('/{record}/edit'),
        ];
    }
}
