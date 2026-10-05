<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\EventoResource\Pages;
use App\Models\Evento;
use App\Support\CodigoQr;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/** Agenda (RF-15): fecha, lugar, comuna, tipo, cupo, imagen, QR e inscritos. */
class EventoResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'contenido.gestionar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = Evento::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Contenido';

    protected static ?string $modelLabel = 'evento';

    protected static ?string $pluralModelLabel = 'Agenda';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('titulo')->label('Título')->required()->maxLength(120)->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                Forms\Components\TextInput::make('slug')->label('Ruta')->prefix('/agenda/')->required()->maxLength(160)->unique(ignoreRecord: true)->alphaDash(),
                Forms\Components\Select::make('tipo')->options(['encuentro' => 'Encuentro', 'recorrido' => 'Recorrido', 'foro' => 'Foro', 'reunion' => 'Reunión', 'otro' => 'Otro'])->default('encuentro')->required()->native(false),
                Forms\Components\Select::make('estado')->options(['borrador' => 'Borrador', 'publicado' => 'Publicado', 'cancelado' => 'Cancelado', 'realizado' => 'Realizado'])->default('borrador')->required()->native(false),
                Forms\Components\DateTimePicker::make('inicia_en')->label('Inicia')->required()->seconds(false)->native(false),
                Forms\Components\DateTimePicker::make('termina_en')->label('Termina')->seconds(false)->native(false)->after('inicia_en'),
                Forms\Components\TextInput::make('lugar')->maxLength(160),
                Forms\Components\TextInput::make('direccion')->label('Dirección')->maxLength(200),
                Forms\Components\Select::make('comuna_id')->label('Comuna')->relationship('comuna', 'nombre', fn ($query) => $query->where('division', '2024'))->native(false),
                Forms\Components\TextInput::make('cupo')->numeric()->minValue(1),
                Forms\Components\Toggle::make('publico')->label('Visible en el sitio')->default(true),
            ]),
            Forms\Components\RichEditor::make('descripcion')->label('Descripción')->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),
            SpatieMediaLibraryFileUpload::make('imagen')->collection('imagen')->image()->imageEditor()->maxSize(4096),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('inicia_en', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount([
                'asistencias as inscritos_count' => fn ($q) => $q->where('estado', '!=', 'cancelo'),
                'asistencias as asistentes_count' => fn ($q) => $q->where('estado', 'asistio'),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('titulo')->label('Título')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('inicia_en')->label('Fecha')->dateTime('d M Y g:i a')->sortable(),
                Tables\Columns\TextColumn::make('comuna.nombre')->label('Comuna')->limit(20),
                Tables\Columns\TextColumn::make('estado')->badge(),
                Tables\Columns\TextColumn::make('inscritos_count')->label('Inscritos')->state(fn (Evento $r) => $r->inscritos_count.($r->cupo ? ' / '.$r->cupo : '')),
                Tables\Columns\TextColumn::make('asistentes_count')->label('Asistieron'),
            ])
            ->actions([
                Tables\Actions\Action::make('qr')->label('QR')->icon('heroicon-o-qr-code')
                    ->action(fn (Evento $record) => response()->streamDownload(fn () => print (CodigoQr::png(route('agenda.show', $record).'?utm_source=qr&utm_medium=evento')), 'qr-'.$record->slug.'.png')),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventos::route('/'),
            'create' => Pages\CreateEvento::route('/create'),
            'edit' => Pages\EditEvento::route('/{record}/edit'),
        ];
    }
}
