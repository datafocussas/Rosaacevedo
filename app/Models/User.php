<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    public const ROLES = [
        'administrador' => 'Administrador (Tecnología)',
        'editor' => 'Editor (Comunicaciones)',
        'moderador' => 'Moderador (Escucha)',
        'analista' => 'Analista (Estrategia)',
    ];

    protected $fillable = ['name', 'email', 'password', 'activo'];

    protected $hidden = ['password', 'remember_token', 'dos_factores_secreto'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dos_factores_secreto' => 'encrypted',
            'dos_factores_confirmado_en' => 'datetime',
            'ultimo_acceso_en' => 'datetime',
            'bloqueado_hasta' => 'datetime',
            'activo' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->activo && $this->roles()->exists();
    }

    public function tieneDosFactores(): bool
    {
        return $this->dos_factores_confirmado_en !== null && $this->dos_factores_secreto !== null;
    }

    public function esAdministrador(): bool
    {
        return $this->hasRole('administrador');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'email', 'activo'])->logOnlyDirty()->useLogName('usuarios');
    }
}
