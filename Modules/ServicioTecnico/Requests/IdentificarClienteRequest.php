<?php

namespace Modules\ServicioTecnico\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IdentificarClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:100'],
            'telefono' => ['required', 'string', 'min:7', 'max:20'],
            'correo' => ['required', 'email:rfc', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Debes indicar tu nombre completo.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'telefono.required' => 'Debes indicar tu número de celular.',
            'telefono.min' => 'El celular debe tener al menos 7 caracteres.',
            'correo.required' => 'Debes indicar tu correo electrónico para recibir el código.',
            'correo.email' => 'El correo electrónico no tiene un formato válido.',
            'correo.max' => 'El correo no puede exceder los 150 caracteres.',
        ];
    }
}
