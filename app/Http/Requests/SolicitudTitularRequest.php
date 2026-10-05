<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\VerificaAntispam;
use App\Models\SolicitudTitular;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SolicitudTitularRequest extends FormRequest
{
    use VerificaAntispam;

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(array_keys(SolicitudTitular::TIPOS))],
            'nombre' => ['required', 'string', 'min:2', 'max:120'],
            'contacto' => ['required', 'string', 'max:160'],
            'detalle' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'Elige qué quieres hacer con tus datos.',
            'nombre.required' => 'Escribe tu nombre.',
            'contacto.required' => 'Escribe el celular o el correo con el que te registraste.',
        ];
    }
}
