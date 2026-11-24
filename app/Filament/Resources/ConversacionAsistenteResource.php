<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\ConversacionAsistenteResource\Pages;
use App\Models\ConversacionAsistente;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Conversaciones del asistente, solo lectura. Sirven para ver qué pregunta la gente y qué le falta a la base de
 * conocimiento. Pueden traer datos que la persona escribió por su cuenta: solo las ve quien puede ver registros.
 */
class ConversacionAsistenteResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'registros.ver';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = ConversacionAsistente::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Asistente';

    protected static ?string $navigationLabel = 'Conversaciones';

    protected static ?string $modelLabel = 'conversación';

    protected static ?string $pluralModelLabel = 'Conversaciones';

    protected static ?string $slug = 'asistente/conversaciones';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canView(Model $record): bool
    {
        return static::puede();
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['mensajesLista' => fn ($q) => $q->where('rol', 'usuario')->limit(1)]))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Inicio')->dateTime('d M Y g:i a')->sortable(),
                Tables\Columns\TextColumn::make('primera')->label('Primera pregunta')->wrap()
                    ->state(fn (ConversacionAsistente $r) => Str::limit($r->mensajesLista->first()?->contenido, 120)),
                Tables\Columns\TextColumn::make('mensajes')->label('Preguntas')->sortable(),
                Tables\Columns\TextColumn::make('pagina')->label('Desde')->placeholder('—'),
                Tables\Columns\TextColumn::make('sin_respuesta')->label('Sin respuesta')->badge()->color('warning')
                    ->state(fn (ConversacionAsistente $r) => $r->mensajesLista()->where('rol', 'asistente')->where('estado', '!=', 'ok')->count() ?: null)
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\Filter::make('con_problemas')->label('Con respuestas fallidas o límites')
                    ->query(fn ($query) => $query->whereHas('mensajesLista', fn ($q) => $q->where('estado', '!=', 'ok'))),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->columns(4)->schema([
                Infolists\Components\TextEntry::make('created_at')->label('Inicio')->dateTime('d M Y g:i a'),
                Infolists\Components\TextEntry::make('pagina')->label('Abierta desde')->placeholder('—'),
                Infolists\Components\TextEntry::make('mensajes')->label('Preguntas'),
                Infolists\Components\TextEntry::make('tokens')->label('Tokens')
                    ->state(fn (ConversacionAsistente $r) => number_format($r->tokens_entrada, 0, ',', '.').' entrada · '.number_format($r->tokens_salida, 0, ',', '.').' salida'),
            ]),
            Infolists\Components\Section::make('Conversación')->schema([
                Infolists\Components\RepeatableEntry::make('mensajesLista')->label('')->contained(false)->schema([
                    Infolists\Components\TextEntry::make('contenido')->hiddenLabel()
                        ->prefix(fn ($record) => $record->rol === 'usuario' ? 'Persona: ' : 'Asistente: ')
                        ->weight(fn ($record) => $record->rol === 'usuario' ? 'bold' : null)
                        ->badge(fn ($record) => $record->estado !== 'ok')
                        ->color(fn ($record) => $record->estado !== 'ok' ? 'warning' : null)
                        ->helperText(fn ($record) => $record->rol === 'asistente' && $record->estado !== 'ok'
                            ? ['error' => 'El modelo no respondió', 'limite' => 'Límite alcanzado', 'rechazo' => 'El modelo declinó la pregunta'][$record->estado] ?? null
                            : $record->created_at?->format('g:i a')),
                ]),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversaciones::route('/'),
            'view' => Pages\VerConversacion::route('/{record}'),
        ];
    }
}
