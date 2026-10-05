<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\BitacoraResource\Pages;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

/** Bitácora (RF-42): solo lectura y solo Administrador. */
class BitacoraResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'sistema.administrar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Sitio';

    protected static ?string $modelLabel = 'registro de bitácora';

    protected static ?string $pluralModelLabel = 'Bitácora';

    protected static ?string $slug = 'bitacora';

    protected static ?int $navigationSort = 8;

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

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('Y-m-d H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('causer.name')->label('Quién')->placeholder('Sistema')->searchable(),
                Tables\Columns\TextColumn::make('log_name')->label('Grupo')->badge(),
                Tables\Columns\TextColumn::make('description')->label('Acción')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('subject_type')->label('Sobre')->formatStateUsing(fn ($state, $record) => class_basename((string) $state).' #'.$record->subject_id)->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')->label('Grupo')->options(fn () => Activity::query()->distinct()->pluck('log_name', 'log_name')->filter()->all()),
            ])
            ->actions([Tables\Actions\ViewAction::make()]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('created_at')->label('Fecha')->dateTime('Y-m-d H:i:s'),
            Infolists\Components\TextEntry::make('causer.name')->label('Quién')->placeholder('Sistema'),
            Infolists\Components\TextEntry::make('description')->label('Acción'),
            Infolists\Components\TextEntry::make('properties')->label('Detalle')->columnSpanFull()
                ->state(fn ($record) => json_encode($record->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                ->fontFamily('mono'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListBitacora::route('/')];
    }
}
