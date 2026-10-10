<?php

namespace App\Http\Requests;

use App\Models\Voluntariado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroCompletarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:400'],
            'email' => ['nullable', 'email:rfc', 'max:160'],
            'barrio_id' => ['nullable', 'integer', Rule::exists('territorio_barrio', 'id')->where('activo', true)],
            'comuna_id' => ['nullable', 'integer', Rule::exists('territorio_comuna', 'id')->where('division', '2024')],
            'intereses' => ['nullable', 'array', 'max:10'],
            'intereses.*' => ['string', Rule::in(array_keys(Voluntariado::INTERESES))],
            'disponibilidad' => ['nullable', 'array', 'max:5'],
            'disponibilidad.*' => ['string', Rule::in(array_keys(Voluntariado::DISPONIBILIDAD))],
            'puesto_votacion' => ['nullable', 'string', 'max:160'],
            'consent.afinidad' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['token' => $this->route('token') ?? ($this->hasSession() ? $this->session()->get('registro.token') : null)]);
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Revisa tu correo: parece incompleto.',
            'barrio_id.exists' => 'Elige un barrio o vereda de la lista.',
            'comuna_id.exists' => 'Elige tu comuna o el corregimiento de la lista.',
        ];
    }

    public function datos(): array
    {
        return [
            'email' => $this->input('email') ? mb_strtolower(trim($this->input('email'))) : null,
            'barrio_id' => $this->input('barrio_id') ? (int) $this->input('barrio_id') : null,
            'comuna_id' => $this->input('comuna_id') ? (int) $this->input('comuna_id') : null,
            'intereses' => $this->input('intereses', []),
            'disponibilidad' => $this->input('disponibilidad') ?: null,
            'puesto_votacion' => $this->filled('puesto_votacion') ? trim(strip_tags($this->input('puesto_votacion'))) : null,
            'consent_afinidad' => $this->boolean('consent.afinidad'),
        ];
    }
}
