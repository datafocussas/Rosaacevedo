<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CrearUsuario extends Command
{
    protected $signature = 'usuarios:crear {email} {--nombre=} {--rol=administrador}';

    protected $description = 'Crea un usuario del panel con su rol (administrador, editor, moderador o analista).';

    public function handle(): int
    {
        $rol = $this->option('rol');
        if (! array_key_exists($rol, User::ROLES)) {
            $this->error('Rol no válido. Usa: '.implode(', ', array_keys(User::ROLES)));

            return self::FAILURE;
        }

        $clave = $this->secret('Contraseña (mínimo 12 caracteres, mayúsculas, minúsculas y números)');
        $validador = Validator::make(['email' => $this->argument('email'), 'clave' => $clave], [
            'email' => ['required', 'email', 'unique:users,email'],
            'clave' => ['required', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        if ($validador->fails()) {
            foreach ($validador->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $usuario = User::query()->create([
            'name' => $this->option('nombre') ?: $this->argument('email'),
            'email' => $this->argument('email'),
            'password' => $clave,
            'activo' => true,
        ]);
        $usuario->assignRole($rol);

        $this->info("Usuario creado con rol {$rol}. Configurará el doble factor en su primer ingreso a /admin.");

        return self::SUCCESS;
    }
}
