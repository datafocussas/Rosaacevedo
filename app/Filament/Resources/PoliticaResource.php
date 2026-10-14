<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\PoliticaResource\Pages;
use App\Models\Politica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Versiones de la política de datos y de los textos de autorización. Solo una vigente por tipo; una
 * versión con consentimientos queda de solo lectura (los consentimientos apuntan a su texto exacto).
 */
class PoliticaResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'sistema.administrar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Politica::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Sitio';

    protected static ?string $modelLabel = 'versión de texto legal';

    protected static ?string $pluralModelLabel = 'Políticas';

    protected static ?int $navigationSort = 4;

    public static function canEdit(Model $record): bool
    {
        return static::puede() && ! $record->tieneConsentimientos() && ! $record->vigente;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(3)->schema([
                Forms\Components\Select::make('tipo')->options(Politica::TIPOS)->required()->native(false),
                Forms\Components\TextInput::make('version')->label('Versión')->required()->maxLength(10)->placeholder('1.0'),
                Forms\Components\DateTimePicker::make('vigente_desde')->label('Vigente desde')->required()->default(now())->seconds(false)->native(false),
                Forms\Components\Textarea::make('texto')->required()->rows(18)->columnSpanFull()
                    ->helperText('Política completa en Markdown. Para las casillas, el texto exacto que ve la persona. Aprobado por el asesor jurídico.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('tipo')
            ->columns([
                Tables\Columns\TextColumn::make('tipo')->formatStateUsing(fn ($state) => Politica::TIPOS[$state])->searchable(),
                Tables\Columns\TextColumn::make('version')->label('Versión'),
                Tables\Columns\IconColumn::make('vigente')->boolean(),
                Tables\Columns\TextColumn::make('vigente_desde')->label('Desde')->dateTime('d M Y'),
                Tables\Columns\TextColumn::make('hash_sha256')->label('Huella')->limit(12)->tooltip(fn ($state) => $state),
                Tables\Columns\TextColumn::make('consentimientos')->label('Consentimientos')
                    ->state(fn (Politica $r) => DB::table('consentimientos')->where('politica_id', $r->id)->count()),
            ])
            ->filters([Tables\Filters\SelectFilter::make('tipo')->options(Politica::TIPOS)])
            ->actions([
                Tables\Actions\Action::make('activar')->label('Hacer vigente')->icon('heroicon-o-check-badge')
                    ->visible(fn (Politica $r) => ! $r->vigente)->requiresConfirmation()
                    ->modalDescription('Desde ahora los formularios mostrarán este texto y los nuevos consentimientos quedarán con esta versión.')
                    ->action(function (Politica $record) {
                        $record->activar();
                        activity('legal')->causedBy(auth()->user())->performedOn($record)->log('Activó la versión '.$record->version.' de '.$record->tipo);
                        Notification::make()->title('Versión vigente actualizada')->success()->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPoliticas::route('/'),
            'create' => Pages\CreatePolitica::route('/create'),
            'edit' => Pages\EditPolitica::route('/{record}/edit'),
        ];
    }
}
