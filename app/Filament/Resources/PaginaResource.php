<?php

namespace App\Filament\Resources;

use App\Filament\Bloques;
use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\PaginaResource\Pages;
use App\Models\Pagina;
use App\Services\MenuPaginas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/** Páginas por bloques (RF-12), incluida la página de inicio. */
class PaginaResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Pagina::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $pluralModelLabel = 'Páginas';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?string $modelLabel = 'página';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(3)->schema([
                Forms\Components\TextInput::make('titulo')->label('Título')->required()->maxLength(160)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state, string $operation) {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                Forms\Components\TextInput::make('slug')->label('Dirección de la página')->required()->maxLength(120)
                    ->prefix(fn () => preg_replace('#^https?://#', '', url('/')).'/')
                    ->helperText(fn (?Pagina $record) => $record && in_array($record->slug, Pagina::FIJAS, true)
                        ? 'Esta página tiene dirección fija.'
                        : 'Se llena sola con el título. Solo minúsculas, números y guiones: «nuestro-equipo» queda en rosaacevedo.com/nuestro-equipo.')
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->notIn(Pagina::RESERVADAS)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'regex' => 'Usa solo minúsculas sin tildes, números y guiones (por ejemplo «nuestro-equipo»).',
                        'not_in' => 'Esa dirección ya la usa otra sección del sitio. Elige otra.',
                        'unique' => 'Ya hay una página con esa dirección.',
                    ])
                    ->disabled(fn (?Pagina $record) => $record && in_array($record->slug, Pagina::FIJAS, true))
                    ->dehydrated(),
                Forms\Components\Select::make('estado')->options(['borrador' => 'Borrador', 'publicada' => 'Publicada', 'archivada' => 'Archivada'])->required()->default('borrador')->native(false)
                    ->helperText('Solo las páginas «Publicada» se ven en el sitio. Usa «Vista previa» para revisar un borrador.'),
            ]),
            Forms\Components\Section::make('Menú')
                ->description('Dónde aparece el enlace a esta página. También se ordena en Sitio → Menú.')
                ->hidden(fn (?Pagina $record) => $record?->slug === 'inicio')
                ->columns(2)
                ->schema([
                    Forms\Components\CheckboxList::make('menu_ubicaciones')->label('Mostrar en')
                        ->options(MenuPaginas::UBICACIONES)
                        ->dehydrated(false)
                        ->rule(fn (?Pagina $record) => function (string $atributo, $valor, \Closure $fallar) use ($record) {
                            if (in_array('principal', (array) $valor, true) && ! app(MenuPaginas::class)->cabeEnPrincipal($record?->ruta())) {
                                $fallar('El menú principal ya tiene '.MenuPaginas::MAX_PRINCIPAL.' ítems. Quita uno en Sitio → Menú o elige el pie.');
                            }
                        }),
                    Forms\Components\TextInput::make('menu_texto')->label('Texto del enlace')->maxLength(60)->dehydrated(false)
                        ->placeholder('Si lo dejas vacío, se usa el título')
                        ->helperText('Corto: dos o tres palabras.'),
                ]),
            Bloques::builder('bloques', conInicio: true)->columnSpanFull(),
            Forms\Components\Section::make('SEO y redes')->collapsed()->columns(2)->schema([
                Forms\Components\TextInput::make('seo_titulo')->label('Título SEO')->maxLength(70),
                Forms\Components\TextInput::make('seo_descripcion')->label('Descripción SEO')->maxLength(160),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Dirección')->formatStateUsing(fn ($state, Pagina $record) => $record->ruta()),
                Tables\Columns\TextColumn::make('menu')->label('En el menú')->badge()->placeholder('No')
                    ->state(fn (Pagina $record) => collect(app(MenuPaginas::class)->ubicaciones($record))->map(fn ($u) => MenuPaginas::UBICACIONES[$u])->all()),
                Tables\Columns\TextColumn::make('estado')->badge()->formatStateUsing(fn ($state) => ucfirst($state))->color(fn ($state) => match ($state) {
                    'publicada' => 'success', 'borrador' => 'gray', default => 'warning'
                }),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizada')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')->label('Ver')->icon('heroicon-o-arrow-top-right-on-square')
                    ->visible(fn (Pagina $record) => $record->estado === 'publicada')
                    ->url(fn (Pagina $record) => url($record->ruta()), true),
                Tables\Actions\Action::make('vista_previa')->label('Vista previa')->icon('heroicon-o-eye')
                    ->url(fn (Pagina $record) => URL::temporarySignedRoute('vista-previa.pagina', now()->addHours(2), ['pagina' => $record->id]), true),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaginas::route('/'),
            'create' => Pages\CreatePagina::route('/create'),
            'edit' => Pages\EditPagina::route('/{record}/edit'),
        ];
    }
}
