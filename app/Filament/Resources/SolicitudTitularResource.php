<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\SolicitudTitularResource\Pages;
use App\Models\SolicitudTitular;
use App\Services\Titular;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Solicitudes del titular (RF-44) con semáforo de vencimiento (10 y 15 días hábiles). */
class SolicitudTitularResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'titular.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = SolicitudTitular::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Ciudadanía';

    protected static ?string $modelLabel = 'solicitud del titular';

    protected static ?string $pluralModelLabel = 'Solicitudes del titular';

    protected static ?int $navigationSort = 3;

    public const ESTADOS = ['abierta' => 'Abierta', 'en_tramite' => 'En trámite', 'respondida' => 'Respondida', 'cerrada' => 'Cerrada'];

    public const SEMAFORO = ['vencida' => 'Vencida', 'por_vencer' => 'Por vencer', 'en_plazo' => 'En plazo', 'cerrada' => 'Cerrada'];

    public static function getNavigationBadge(): ?string
    {
        $n = SolicitudTitular::query()->whereIn('estado', ['abierta', 'en_tramite'])->where('vence_en', '<=', now()->addDays(5))->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(3)->schema([
                Forms\Components\TextInput::make('radicado')->disabled()->dehydrated(false)->visibleOn('edit'),
                Forms\Components\Select::make('tipo')->options(SolicitudTitular::TIPOS)->required()->native(false)->disabledOn('edit'),
                Forms\Components\DatePicker::make('vence_en')->label('Vence')->disabled()->dehydrated(false)->visibleOn('edit'),
                Forms\Components\TextInput::make('nombre')->required()->maxLength(120)->disabledOn('edit'),
                Forms\Components\TextInput::make('contacto')->required()->maxLength(160)->disabledOn('edit'),
                Forms\Components\Select::make('estado')->options(self::ESTADOS)->default('abierta')->required()->native(false),
                Forms\Components\Textarea::make('detalle')->columnSpanFull()->disabledOn('edit'),
                Forms\Components\Textarea::make('respuesta')->columnSpanFull()->rows(4),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('vence_en')
            ->columns([
                Tables\Columns\TextColumn::make('radicado')->searchable(),
                Tables\Columns\TextColumn::make('tipo')->formatStateUsing(fn ($state) => SolicitudTitular::TIPOS[$state]),
                Tables\Columns\TextColumn::make('nombre')->searchable(),
                Tables\Columns\TextColumn::make('estado')->badge()->formatStateUsing(fn ($state) => self::ESTADOS[$state]),
                Tables\Columns\TextColumn::make('semaforo')->label('Plazo')->badge()
                    ->state(fn (SolicitudTitular $r) => self::SEMAFORO[$r->semaforo()])
                    ->color(fn (SolicitudTitular $r) => match ($r->semaforo()) {
                        'vencida' => 'danger', 'por_vencer' => 'warning', 'en_plazo' => 'success', default => 'gray'
                    })
                    ->icon(fn (SolicitudTitular $r) => match ($r->semaforo()) {
                        'vencida' => 'heroicon-o-exclamation-triangle', 'por_vencer' => 'heroicon-o-clock', default => 'heroicon-o-check-circle'
                    }),
                Tables\Columns\TextColumn::make('vence_en')->label('Vence')->date('d M Y')->sortable(),
                Tables\Columns\IconColumn::make('ciudadano_id')->label('Ficha vinculada')->boolean()->state(fn ($r) => (bool) $r->ciudadano_id),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options(self::ESTADOS)->default(null),
                Tables\Filters\SelectFilter::make('tipo')->options(SolicitudTitular::TIPOS),
            ])
            ->actions([
                Tables\Actions\Action::make('aplicar')->label('Aplicar retiro')->icon('heroicon-o-no-symbol')->color('danger')
                    ->visible(fn (SolicitudTitular $r) => in_array($r->tipo, ['revocatoria', 'supresion'], true) && $r->ciudadano_id && $r->ciudadano?->estado !== 'retirado')
                    ->requiresConfirmation()
                    ->modalDescription('Confirma que verificaste la identidad del titular. Se registra la revocatoria de todas sus autorizaciones, se marca como retirado y se avisa al CRM.')
                    ->action(function (SolicitudTitular $record) {
                        app(Titular::class)->retirar($record, request());
                        Notification::make()->title('Autorizaciones revocadas y CRM notificado')->success()->send();
                    }),
                Tables\Actions\EditAction::make()->label('Atender'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitudTitulars::route('/'),
            'create' => Pages\CreateSolicitudTitular::route('/create'),
            'edit' => Pages\EditSolicitudTitular::route('/{record}/edit'),
        ];
    }
}
