<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Banners por página (RF-10): escritorio 16:9 y móvil 4:5, titular HTML, dos botones, lema, variante A/B,
 * ventana de publicación y orden por arrastre. Reemplazar una imagen conserva el banner y su historial.
 */
class BannerResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $pluralModelLabel = 'Banners';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Texto')->columns(2)->schema([
                Forms\Components\TextInput::make('etiqueta')->maxLength(60)->placeholder('Por el futuro de Itagüí'),
                Forms\Components\TextInput::make('titular')->required()->maxLength(70)->helperText('Va como texto HTML, nunca dentro de la imagen. Sin «¡!».'),
                Forms\Components\Textarea::make('texto')->maxLength(160)->rows(2)->columnSpanFull(),
                Forms\Components\Toggle::make('mostrar_lema')->label('Mostrar «Aquí me planto.» con la rosa'),
            ]),
            Forms\Components\Section::make('Botones')->columns(2)->schema([
                Forms\Components\TextInput::make('btn1_texto')->label('Botón principal: texto')->maxLength(30),
                Forms\Components\TextInput::make('btn1_url')->label('Botón principal: enlace')->maxLength(255),
                Forms\Components\TextInput::make('btn2_texto')->label('Botón secundario: texto')->maxLength(30),
                Forms\Components\TextInput::make('btn2_url')->label('Botón secundario: enlace')->maxLength(255),
            ]),
            Forms\Components\Section::make('Imágenes')->columns(2)->schema([
                SpatieMediaLibraryFileUpload::make('escritorio')->collection('escritorio')->label('Escritorio (16:9)')->image()->imageEditor()->imageEditorAspectRatios(['16:9'])->maxSize(4096),
                SpatieMediaLibraryFileUpload::make('movil')->collection('movil')->label('Móvil (4:5)')->image()->imageEditor()->imageEditorAspectRatios(['4:5'])->maxSize(4096),
                Forms\Components\TextInput::make('alt')->label('Texto alternativo')->required()->maxLength(200)->columnSpanFull()
                    ->helperText('Obligatorio. Describe la foto: «Rosa conversa con comerciantes en Santa María».'),
            ]),
            Forms\Components\Section::make('Publicación')->columns(3)->schema([
                Forms\Components\Select::make('pagina')->label('Página')->options(['inicio' => 'Inicio', 'comuna' => 'Página de comuna'])->default('inicio')->required()->live()->native(false),
                Forms\Components\Select::make('comuna_id')->label('Comuna')->relationship('comuna', 'nombre', fn ($query) => $query->where('division', '2024'))
                    ->visible(fn (Forms\Get $get) => $get('pagina') === 'comuna')->required(fn (Forms\Get $get) => $get('pagina') === 'comuna')->native(false),
                Forms\Components\Select::make('variante')->label('Variante A/B')->options(['A' => 'A', 'B' => 'B', 'C' => 'C'])->native(false)
                    ->helperText('Déjalo vacío si no está en prueba. Solo aplica en inicio.'),
                Forms\Components\Select::make('estado')->options(['borrador' => 'Borrador', 'activo' => 'Activo', 'archivado' => 'Archivado'])->default('borrador')->required()->native(false),
                Forms\Components\DateTimePicker::make('publicar_desde')->label('Publicar desde')->seconds(false)->native(false),
                Forms\Components\DateTimePicker::make('publicar_hasta')->label('Publicar hasta')->seconds(false)->native(false)->after('publicar_desde'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->reorderable('orden')->defaultSort('orden')
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('escritorio')->collection('escritorio')->label('')->height(54)->width(96),
                Tables\Columns\TextColumn::make('titular')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('pagina')->label('Página')->formatStateUsing(fn ($state, $record) => $state === 'comuna' ? ($record->comuna?->nombre ?? 'Comuna') : 'Inicio'),
                Tables\Columns\TextColumn::make('variante')->badge()->placeholder('—'),
                Tables\Columns\TextColumn::make('estado_publicacion')->label('Estado')->badge()
                    ->state(fn (Banner $r) => match (true) {
                        $r->estado !== 'activo' => ucfirst($r->estado),
                        $r->publicar_desde?->isFuture() => 'Programado',
                        $r->publicar_hasta?->isPast() => 'Vencido',
                        default => 'Publicado',
                    })
                    ->color(fn ($state) => match ($state) {
                        'Publicado' => 'success', 'Programado' => 'warning', default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('publicar_desde')->label('Desde')->dateTime('d M Y g:i a')->placeholder('—'),
                Tables\Columns\TextColumn::make('publicar_hasta')->label('Hasta')->dateTime('d M Y g:i a')->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pagina')->options(['inicio' => 'Inicio', 'comuna' => 'Comuna']),
                Tables\Filters\SelectFilter::make('estado')->options(['borrador' => 'Borrador', 'activo' => 'Activo', 'archivado' => 'Archivado']),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
