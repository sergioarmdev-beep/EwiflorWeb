<?php

namespace Modules\Precios\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTipoCambioManualRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && in_array($user->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true);
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'tipo'  => ['required', Rule::in(['PARALELO', 'OFICIAL'])],
            'valor' => ['required', 'numeric', 'gt:0', 'max:999999.9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.before_or_equal' => 'No puedes registrar un tipo de cambio con fecha futura.',
            'valor.gt'              => 'El valor debe ser mayor a cero.',
            'valor.max'             => 'El valor excede el máximo permitido.',
        ];
    }
}
