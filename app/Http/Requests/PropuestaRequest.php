<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\VerificaAntispam;
use App\Support\Celular;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropuestaRequest extends FormRequest
{
    use VerificaAntispam;

    public function rules(): array
    {
        return [
            'tema_id' => ['required', 'integer', Rule::exists('temas', 'id')->where('activo', true)],
            'barrio_id' => ['nullable', 'integer', Rule::exists('territorio_barrio', 'id')->where('activo', true)],
            'comuna_id' => ['required_without:barrio_id', 'nullable', 'integer', Rule::exists('territorio_comuna', 'id')->where('division', '2024')],
            'texto' => ['required', 'string', 'min:10', 'max:1500'],
            'foto' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'nombre' => ['required', 'string', 'min:2', 'max:120'],
            'celular' => ['required', 'string', 'max:20', function ($atributo, $valor, $fallar) {
                if (! Celular::normalizar($valor)) {
                    $fallar('Escribe los 10 dígitos de tu celular. Debe empezar por 3.');
                }
            }],
            'consent.general' => ['accepted'],
            'consent.publicar' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'tema_id.required' => 'Elige un tema.',
            'comuna_id.required_without' => 'Elige tu comuna o el corregimiento.',
            'comuna_id.exists' => 'Elige tu comuna o el corregimiento de la lista.',
            'texto.required' => 'Escribe tu propuesta.',
            'texto.min' => 'Cuéntanos un poco más: al menos 10 caracteres.',
            'texto.max' => 'Tu propuesta puede tener hasta 1.500 caracteres.',
            'foto.image' => 'La foto debe ser una imagen JPG, PNG o WebP.',
            'foto.max' => 'La foto puede pesar hasta 5 MB.',
            'nombre.required' => 'Escribe tu nombre.',
            'celular.required' => 'Escribe tu celular.',
            'consent.general.accepted' => 'Para recibir tu propuesta necesitamos tu autorización para tratar tus datos.',
        ];
    }
}
