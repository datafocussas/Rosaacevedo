<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CiudadanoResource\Pages;
use App\Models\Ciudadano;
use App\Models\TerritorioComuna;
use App\Support\Celular;
use Filament\Forms\Components\TextInput;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Registros (sección 03). Solo lectura. Datos enmascarados para quien no tenga permiso; el Moderador
 * ve solo nombre y comuna. Cada apertura de ficha y cada exportación queda en la bitácora (RF-42).
 */
class CiudadanoResource extends Resource
{
    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Ciudadano::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Ciudadanía';

    protected static ?string $modelLabel = 'registro';

    protected static ?string $pluralModelLabel = 'Registros';

    protected static ?int $navigationSort = 1;

    public static function verCompleto(): bool
    {
        return (bool) auth()->user()?->can('registros.ver');
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->canAny(['registros.ver', 'registros.ver_limitado']);
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        $completo = static::verCompleto();

        return $table->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('barrio.comuna', 'barrio.comuna2007'))
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable(),
                Tables\Columns\TextColumn::make('celular')->label('Celular')->visible($completo)
                    ->state(fn (Ciudadano $r) => $r->celularEnmascarado())
                    ->searchable(query: function (Builder $query, string $search) {
                        // Búsqueda por celular exacto: se compara el HMAC, nunca el número en claro.
                        $e164 = Celular::normalizar($search);

                        return $e164 ? $query->orWhere('celular_hash', Celular::hmac($e164)) : $query;
                    }),
                Tables\Columns\TextColumn::make('barrio.comuna.nombre')->label('Comuna')->placeholder('Sin barrio')->limit(28),
                Tables\Columns\TextColumn::make('barrio.nombre')->label('Barrio')->visible($completo),
                Tables\Columns\TextColumn::make('paso_alcanzado')->label('Paso')->visible($completo),
                Tables\Columns\IconColumn::make('es_voluntario')->label('Voluntario')->boolean()->visible($completo),
                Tables\Columns\TextColumn::make('estado')->badge()->color(fn ($state) => $state === 'retirado' ? 'danger' : 'success')->visible($completo),
                Tables\Columns\TextColumn::make('crm_sync_estado')->label('CRM')->badge()->visible($completo)
                    ->color(fn ($state) => match ($state) {
                        'sincronizado' => 'success', 'error' => 'danger', default => 'warning'
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Registro')->dateTime('d M Y g:i a')->sortable()->visible($completo),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('comuna')->label('Comuna')
                    ->options(fn () => TerritorioComuna::query()->where('division', '2024')->pluck('nombre', 'id'))
                    ->query(fn (Builder $query, array $data) => $data['value'] ? $query->whereHas('barrio', fn ($b) => $b->where('comuna_2024_id', $data['value'])) : $query),
                Tables\Filters\SelectFilter::make('paso_alcanzado')->label('Paso')->options([1 => 'Paso 1', 2 => 'Paso 2', 3 => 'Paso 3']),
                Tables\Filters\SelectFilter::make('crm_sync_estado')->label('Sincronización')->options(['pendiente' => 'Pendiente', 'sincronizado' => 'Sincronizado', 'error' => 'Error']),
                Tables\Filters\SelectFilter::make('estado')->options(['activo' => 'Activo', 'inactivo' => 'Inactivo', 'retirado' => 'Retirado']),
                Tables\Filters\Filter::make('codigo_q')->label('Con código QR')
                    ->form([TextInput::make('codigo')->label('Código /q/')])
                    ->query(fn (Builder $query, array $data) => filled($data['codigo'] ?? null) ? $query->whereHas('interacciones', fn ($i) => $i->where('codigo_q', $data['codigo'])) : $query),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $completo = static::verCompleto();

        return $infolist->schema([
            Infolists\Components\Section::make('Datos')->columns(3)->schema([
                Infolists\Components\TextEntry::make('nombre'),
                Infolists\Components\TextEntry::make('barrio.comuna.nombre')->label('Comuna (2024)')->placeholder('Sin barrio'),
                Infolists\Components\TextEntry::make('barrio.comuna2007.nombre')->label('Comuna (2007, JAL)')->placeholder('—')->visible($completo),
                Infolists\Components\TextEntry::make('celular')->label('Celular')->state(fn (Ciudadano $r) => Celular::formatear($r->celular()))->copyable()->visible($completo),
                Infolists\Components\TextEntry::make('email')->label('Correo')->placeholder('—')->visible($completo),
                Infolists\Components\TextEntry::make('barrio.nombre')->label('Barrio')->placeholder('—')->visible($completo),
                Infolists\Components\TextEntry::make('codigo')->label('Código')->state(fn (Ciudadano $r) => $r->codigo())->visible($completo),
                Infolists\Components\TextEntry::make('paso_alcanzado')->label('Paso alcanzado')->visible($completo),
                Infolists\Components\TextEntry::make('estado')->badge()->visible($completo),
                Infolists\Components\TextEntry::make('voluntariado.intereses')->label('Voluntariado')->badge()->placeholder('—')->visible($completo),
                Infolists\Components\TextEntry::make('voluntariado.puesto_votacion')->label('Puesto de votación')->placeholder('No lo compartió')->visible($completo),
                Infolists\Components\TextEntry::make('primer_origen')->label('Primer origen')->visible($completo)
                    ->state(fn (Ciudadano $r) => collect($r->primer_origen ?? [])->map(fn ($v, $k) => "$k: $v")->join(' · ') ?: '—'),
            ]),
            Infolists\Components\Section::make('Consentimientos con evidencia')->visible($completo)->schema([
                Infolists\Components\RepeatableEntry::make('consentimientos')->label('')->columns(6)->schema([
                    Infolists\Components\TextEntry::make('politica.tipo')->label('Autorización'),
                    Infolists\Components\TextEntry::make('politica.version')->label('Versión'),
                    Infolists\Components\IconEntry::make('otorgado')->boolean(),
                    Infolists\Components\TextEntry::make('formulario'),
                    Infolists\Components\TextEntry::make('ip')->label('IP')->state(fn ($record) => $record->ipLegible()),
                    Infolists\Components\TextEntry::make('created_at')->label('Fecha y hora')->dateTime('Y-m-d H:i:s.v'),
                    Infolists\Components\TextEntry::make('user_agent')->label('Agente de usuario')->columnSpan(3)->size('xs'),
                    Infolists\Components\TextEntry::make('politica.hash_sha256')->label('Huella del texto')->columnSpan(3)->size('xs'),
                ]),
            ]),
            Infolists\Components\Section::make('Interacciones y origen')->visible($completo)->collapsed()->schema([
                Infolists\Components\RepeatableEntry::make('interacciones')->label('')->columns(5)->schema([
                    Infolists\Components\TextEntry::make('tipo'),
                    Infolists\Components\TextEntry::make('created_at')->label('Fecha')->dateTime('d M Y g:i a'),
                    Infolists\Components\TextEntry::make('utm_source')->label('Fuente')->placeholder('—'),
                    Infolists\Components\TextEntry::make('codigo_q')->label('Código /q/')->placeholder('—'),
                    Infolists\Components\TextEntry::make('variante')->placeholder('—'),
                ]),
            ]),
            Infolists\Components\Section::make('Sincronización con el CRM')->visible($completo)->columns(3)->schema([
                Infolists\Components\TextEntry::make('crm_sync_estado')->label('Estado')->badge(),
                Infolists\Components\TextEntry::make('crm_id')->label('ID en el CRM')->placeholder('—'),
                Infolists\Components\TextEntry::make('crm_sync_en')->label('Última sincronización')->dateTime()->placeholder('—'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCiudadanos::route('/'),
            'view' => Pages\VerCiudadano::route('/{record}'),
        ];
    }
}
