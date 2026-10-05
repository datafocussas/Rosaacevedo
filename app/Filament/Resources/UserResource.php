<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ConPermiso;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;

/** Usuarios, roles, doble factor y último acceso. */
class UserResource extends Resource
{
    use ConPermiso;

    protected static string $permiso = 'sistema.administrar';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Sitio';

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'Usuarios y roles';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(120),
                Forms\Components\TextInput::make('email')->label('Correo')->email()->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('roles')->label('Rol')->relationship('roles', 'name')->required()->native(false)
                    ->getOptionLabelFromRecordUsing(fn ($record) => User::ROLES[$record->name] ?? $record->name),
                Forms\Components\TextInput::make('password')->label('Contraseña')->password()->revealable()
                    ->required(fn (string $operation) => $operation === 'create')->dehydrated(fn ($state) => filled($state))
                    ->rule(Password::min(12)->letters()->mixedCase()->numbers()->uncompromised())
                    ->helperText('Mínimo 12 caracteres con mayúsculas, minúsculas y números.'),
                Forms\Components\Toggle::make('activo')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Correo')->searchable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Rol')->badge()->formatStateUsing(fn ($state) => User::ROLES[$state] ?? $state),
                Tables\Columns\IconColumn::make('dos_factores')->label('Doble factor')->boolean()->state(fn (User $r) => $r->tieneDosFactores()),
                Tables\Columns\TextColumn::make('ultimo_acceso_en')->label('Último acceso')->since()->placeholder('Nunca'),
                Tables\Columns\IconColumn::make('activo')->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('reiniciar2fa')->label('Reiniciar doble factor')->icon('heroicon-o-key')->color('warning')
                    ->requiresConfirmation()->visible(fn (User $r) => $r->tieneDosFactores())
                    ->action(function (User $record) {
                        $record->forceFill(['dos_factores_secreto' => null, 'dos_factores_confirmado_en' => null])->save();
                        activity('seguridad')->causedBy(auth()->user())->performedOn($record)->log('Reinició el doble factor');
                        Notification::make()->title('El usuario configurará el doble factor en su próximo ingreso')->success()->send();
                    }),
                Tables\Actions\Action::make('desbloquear')->icon('heroicon-o-lock-open')
                    ->visible(fn (User $r) => $r->bloqueado_hasta?->isFuture())
                    ->action(fn (User $record) => $record->forceFill(['bloqueado_hasta' => null, 'intentos_fallidos' => 0])->save()),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
