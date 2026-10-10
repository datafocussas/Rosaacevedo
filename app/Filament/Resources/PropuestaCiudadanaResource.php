<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropuestaCiudadanaResource\Pages;
use App\Models\PropuestaCiudadana;
use App\Models\TerritorioComuna;
use App\Models\User;
use App\Services\Crm\Outbox;
use App\Services\Crm\Payloads;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * Bandeja de propuestas (RF-21): estados, asignación, nota interna, respuesta, incorporar, publicar anónima.
 * Sin datos personales en la bandeja: el autor se ve solo con permiso de registros.
 */
class PropuestaCiudadanaResource extends Resource
{
    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = PropuestaCiudadana::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Ciudadanía';

    protected static ?string $modelLabel = 'propuesta ciudadana';

    protected static ?string $pluralModelLabel = 'Propuestas ciudadanas';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('propuestas.ver');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('propuestas.moderar');
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $n = PropuestaCiudadana::query()->where('estado', 'recibida')->count();

        return $n ? (string) $n : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Propuesta')->columns(3)->schema([
                Forms\Components\Placeholder::make('codigo')->label('Código')->content(fn (PropuestaCiudadana $r) => $r->codigo),
                Forms\Components\Placeholder::make('tema')->label('Tema elegido')->content(fn (PropuestaCiudadana $r) => $r->tema?->nombre),
                Forms\Components\Placeholder::make('territorio')->label('Comuna · barrio')->content(fn (PropuestaCiudadana $r) => collect([
                    ($r->comuna ?? $r->barrio?->comuna)?->nombre ?? 'Sin comuna',
                    $r->barrio?->nombre,
                ])->filter()->join(' · ')),
                Forms\Components\Placeholder::make('texto')->label('Texto')->content(fn (PropuestaCiudadana $r) => $r->texto)->columnSpanFull(),
                Forms\Components\Placeholder::make('foto')->label('Foto adjunta')->columnSpanFull()
                    ->content(function (PropuestaCiudadana $r) {
                        $foto = $r->getFirstMedia('foto');
                        if (! $foto) {
                            return 'La persona no adjuntó foto.';
                        }
                        if (! is_readable($foto->getPath())) {
                            return 'La foto está registrada, pero el archivo no está en el servidor.';
                        }

                        return new HtmlString(sprintf(
                            '<a href="%1$s" target="_blank" rel="noopener"><img src="%1$s" alt="Foto adjunta a la propuesta %2$s" style="max-width:100%%;max-height:480px;border-radius:12px"></a><br><a href="%1$s" target="_blank" rel="noopener" style="text-decoration:underline">Abrir en tamaño completo</a>',
                            e(route('propuesta.foto', $r)), e($r->codigo)
                        ));
                    }),
                Forms\Components\Placeholder::make('autor')->label('Autor')->visible(fn () => auth()->user()->can('registros.ver'))
                    ->content(fn (PropuestaCiudadana $r) => $r->ciudadano->nombre.' · '.$r->ciudadano->celularEnmascarado()),
                Forms\Components\Placeholder::make('publicable')->label('¿Autorizó publicarla sin su nombre?')->content(fn (PropuestaCiudadana $r) => $r->publicar_anonima ? 'Sí' : 'No'),
                Forms\Components\Placeholder::make('sugerencia')->label('Sugerencia de la IA (para confirmar)')
                    ->content(fn (PropuestaCiudadana $r) => $r->sugerencia_ia ? collect($r->sugerencia_ia)->only(['tema', 'comuna', 'sentimiento', 'resumen'])->map(fn ($v, $k) => "$k: $v")->join(' · ') : 'Sin sugerencia (fase 2).'),
            ]),
            Forms\Components\Section::make('Moderación')->columns(2)->schema([
                Forms\Components\Select::make('estado')->options(PropuestaCiudadana::ESTADOS)->required()->native(false),
                Forms\Components\Select::make('tema_confirmado_id')->label('Tema confirmado')->relationship('temaConfirmado', 'nombre')->native(false),
                Forms\Components\Select::make('asignada_a')->label('Asignada a')->native(false)
                    ->options(fn () => User::query()->where('activo', true)->pluck('name', 'id')),
                Forms\Components\Toggle::make('destacada')->label('Destacada (publicar anónima)')
                    ->disabled(fn (PropuestaCiudadana $r) => ! $r->publicar_anonima)
                    ->helperText('Solo si la persona autorizó publicarla sin su nombre.'),
                Forms\Components\Textarea::make('nota_interna')->label('Nota interna')->rows(3),
                Forms\Components\Textarea::make('respuesta')->label('Respuesta al ciudadano')->rows(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('tema', 'comuna', 'barrio.comuna', 'media'))
            ->columns([
                Tables\Columns\TextColumn::make('codigo')->label('Código')->searchable(),
                Tables\Columns\TextColumn::make('texto')->limit(80)->wrap()->searchable(),
                Tables\Columns\IconColumn::make('con_foto')->label('Foto')->boolean()->trueIcon('heroicon-o-photo')->falseIcon('heroicon-o-minus')
                    ->state(fn (PropuestaCiudadana $record) => $record->media->contains('collection_name', 'foto')),
                Tables\Columns\TextColumn::make('tema.nombre')->label('Tema')->badge(),
                Tables\Columns\TextColumn::make('comuna')->label('Comuna')->placeholder('—')
                    ->state(fn (PropuestaCiudadana $record) => ($record->comuna ?? $record->barrio?->comuna)?->nombrePublico()),
                Tables\Columns\TextColumn::make('estado')->badge()->formatStateUsing(fn ($state) => PropuestaCiudadana::ESTADOS[$state])
                    ->color(fn ($state) => match ($state) {
                        'recibida' => 'warning', 'incorporada', 'respondida' => 'success', 'descartada' => 'gray', default => 'info'
                    }),
                Tables\Columns\IconColumn::make('destacada')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Recibida')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options(PropuestaCiudadana::ESTADOS),
                Tables\Filters\SelectFilter::make('tema_id')->label('Tema')->relationship('tema', 'nombre'),
                Tables\Filters\SelectFilter::make('comuna')->label('Comuna')
                    ->options(fn () => TerritorioComuna::query()->where('division', '2024')->pluck('nombre', 'id'))
                    ->query(fn (Builder $query, array $data) => $data['value'] ? $query->where(fn ($q) => $q->where('comuna_id', $data['value'])
                        ->orWhereHas('barrio', fn ($b) => $b->where('comuna_2024_id', $data['value']))) : $query),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('Moderar')]);
    }

    /** Cada cambio de moderación viaja al CRM. */
    public static function despuesDeGuardar(PropuestaCiudadana $propuesta): void
    {
        Outbox::registrar('propuesta', $propuesta->id, 'actualizado', Payloads::propuesta($propuesta->fresh(), 'actualizado'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPropuestaCiudadanas::route('/'),
            'edit' => Pages\EditPropuestaCiudadana::route('/{record}/edit'),
        ];
    }
}
