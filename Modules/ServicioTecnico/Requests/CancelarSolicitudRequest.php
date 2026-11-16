<?php

namespace Modules\ServicioTecnico\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array(
            $this->user()->nombre_rol,
            ['GERENTE', 'ADMINISTRADOR'],
            true
        );
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Debes indicar el motivo de la cancelación.',
            'motivo.string' => 'El motivo debe ser un texto válido.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max' => 'El motivo no puede exceder los 1000 caracteres.',
        ];
    }
}
