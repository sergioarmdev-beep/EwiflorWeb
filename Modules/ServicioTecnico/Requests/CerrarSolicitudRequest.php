<?php

namespace Modules\ServicioTecnico\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CerrarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array(
            $this->user()->nombre_rol,
            ['SERVICIO_TECNICO', 'GERENTE', 'ADMINISTRADOR'],
            true
        );
    }

    public function rules(): array
    {
        return [
            'resultado' => ['required', Rule::in(['REPARACION', 'SERVICIO_PAGADO', 'REEMPLAZO'])],
            'diagnostico' => ['required', 'string', 'min:5', 'max:1000'],
            'costo_servicio_bs' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'resultado.required' => 'Debes indicar el resultado del servicio.',
            'diagnostico.required' => 'Debes escribir el diagnóstico de la reparación.',
            'diagnostico.min' => 'El diagnóstico debe tener al menos 5 caracteres.',
        ];
    }
}
