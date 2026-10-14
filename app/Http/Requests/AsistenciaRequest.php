<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\VerificaAntispam;
use App\Support\Celular;
use Illuminate\Foundation\Http\FormRequest;

class AsistenciaRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Escribe tu nombre.',
            'celular.required' => 'Escribe tu celular.',
            'consent.general.accepted' => 'Para inscribirte necesitamos tu autorización para tratar tus datos.',
        ];
    }
}
