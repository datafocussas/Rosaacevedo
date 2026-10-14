<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login;
use Illuminate\Validation\ValidationException;

/** Ingreso al panel con bloqueo de 15 minutos tras 5 intentos fallidos (RNF-05). */
class Ingresar extends Login
{
    public const MAX_INTENTOS = 5;

    public const MINUTOS_BLOQUEO = 15;

    public function authenticate(): ?LoginResponse
    {
        $datos = $this->form->getState();
        $usuario = User::query()->where('email', $datos['email'] ?? '')->first();

        if ($usuario?->bloqueado_hasta?->isFuture()) {
            throw ValidationException::withMessages([
                'data.email' => 'La cuenta está bloqueada por intentos fallidos. Inténtalo de nuevo después de las '.$usuario->bloqueado_hasta->format('g:i a').'.',
            ]);
        }

        try {
            $respuesta = parent::authenticate();
        } catch (ValidationException $e) {
            if ($usuario) {
                $intentos = $usuario->intentos_fallidos + 1;
                $usuario->forceFill([
                    'intentos_fallidos' => $intentos >= self::MAX_INTENTOS ? 0 : $intentos,
                    'bloqueado_hasta' => $intentos >= self::MAX_INTENTOS ? now()->addMinutes(self::MINUTOS_BLOQUEO) : null,
                ])->saveQuietly();
                activity('seguridad')->performedOn($usuario)->withProperties(['ip' => request()->ip()])->log('Intento de ingreso fallido');
            }

            throw $e;
        }

        $usuario = auth()->user();
        $usuario->forceFill(['intentos_fallidos' => 0, 'bloqueado_hasta' => null, 'ultimo_acceso_en' => now()])->saveQuietly();
        activity('seguridad')->causedBy($usuario)->withProperties(['ip' => request()->ip()])->log('Ingreso al panel');

        return $respuesta;
    }
}
