<?php

namespace App\Http\Requests\Concerns;

use App\Services\Turnstile;
use Illuminate\Validation\Validator;

/** Campo trampa oculto + Cloudflare Turnstile (RF-05). */
trait VerificaAntispam
{
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (filled($this->input('sitio_web'))) {
                    $validator->errors()->add('sitio_web', 'No pudimos procesar el envío.');

                    return;
                }

                $token = $this->input('turnstile') ?? $this->input('cf-turnstile-response');
                if (! app(Turnstile::class)->verificar($token, $this->ip())) {
                    $validator->errors()->add('turnstile', 'Confirma que eres una persona e inténtalo de nuevo.');
                }
            },
        ];
    }
}
