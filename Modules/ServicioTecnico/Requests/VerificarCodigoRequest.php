<?php

namespace Modules\ServicioTecnico\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerificarCodigoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'Debes ingresar el código de 6 dígitos que enviamos a tu correo.',
            'codigo.digits' => 'El código debe tener exactamente 6 dígitos.',
        ];
    }
}
