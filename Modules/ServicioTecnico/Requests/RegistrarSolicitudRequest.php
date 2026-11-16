<?php

namespace Modules\ServicioTecnico\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Se permite desde la web pública (sesión de cliente) o interna.
        return true;
    }

    public function rules(): array
    {
        return [
            'descripcion_falla' => ['required', 'string', 'min:5', 'max:1000'],
            'id_garantia' => ['nullable', 'integer', 'exists:garantia,id_garantia'],
        ];
    }

    public function messages(): array
    {
        return [
            'descripcion_falla.required' => 'Debes describir la falla del equipo.',
            'descripcion_falla.min' => 'La descripción debe tener al menos 5 caracteres.',
        ];
    }
}
