<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Autorización de un recurso del panel por un permiso de la sección 03.
 * El recurso define `protected static string $permiso`. El Administrador pasa por Gate::before.
 */
trait ConPermiso
{
    protected static function puede(): bool
    {
        return (bool) auth()->user()?->can(static::$permiso);
    }

    public static function canViewAny(): bool
    {
        return static::puede();
    }

    public static function canCreate(): bool
    {
        return static::puede();
    }

    public static function canEdit(Model $record): bool
    {
        return static::puede();
    }

    public static function canDelete(Model $record): bool
    {
        return static::puede();
    }

    public static function canDeleteAny(): bool
    {
        return static::puede();
    }
}
