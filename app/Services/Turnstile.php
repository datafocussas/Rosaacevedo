<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Verificación de Cloudflare Turnstile (RF-05). Sin secreto configurado (local) no se exige. */
class Turnstile
{
    public function activo(): bool
    {
        return filled(config('rosa.turnstile.secret'));
    }

    public function verificar(?string $token, ?string $ip = null): bool
    {
        if (! $this->activo()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $respuesta = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('rosa.turnstile.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return (bool) $respuesta->json('success', false);
        } catch (\Throwable $e) {
            // Si Cloudflare no responde no se pierde el registro: quedan el campo trampa y los límites.
            Log::warning('Turnstile no respondió', ['error' => $e->getMessage()]);

            return true;
        }
    }
}
