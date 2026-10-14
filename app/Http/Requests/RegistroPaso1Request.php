<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\VerificaAntispam;
use App\Support\Celular;
use Illuminate\Foundation\Http\FormRequest;

class RegistroPaso1Request extends FormRequest
{
    use VerificaAntispam;

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:120'],
            'celular' => ['required', 'string', 'max:20', function ($atributo, $valor, $fallar) {
                if (! Celular::normalizar($valor)) {
                    $fallar('Escribe los 10 dígitos de tu celular. Debe empezar por 3.');
                }
            }],
            'consent.general' => ['accepted'],
            'consent.whatsapp' => ['nullable', 'boolean'],
            'origen' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Escribe tu nombre.',
            'nombre.min' => 'Escribe tu nombre.',
            'celular.required' => 'Escribe tu celular.',
            'consent.general.accepted' => 'Para sumarte necesitamos tu autorización para tratar tus datos.',
        ];
    }

    public function datos(): array
    {
        return [
            'nombre' => trim(strip_tags($this->input('nombre'))),
            'celular' => $this->input('celular'),
            'consent_whatsapp' => $this->boolean('consent.whatsapp'),
        ];
    }
}
