<?php

namespace Modules\Precios\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltroHistorialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso lo controla el middleware 'auth' de la ruta
    }

    public function rules(): array
    {
        return [
            'tipo'   => ['nullable', Rule::in(['PARALELO', 'OFICIAL'])],
            'origen' => ['nullable', Rule::in(['API', 'MANUAL'])],
            'desde'  => ['nullable', 'date'],
            'hasta'  => ['nullable', 'date', 'after_or_equal:desde'],
        ];
    }

    public function messages(): array
    {
        return [
            'hasta.after_or_equal' => 'La fecha "hasta" no puede ser anterior a "desde".',
        ];
    }

    /** Solo los filtros que vinieron con valor. */
    public function filtros(): array
    {
        return array_filter($this->validated(), fn ($v) => $v !== null && $v !== '');
    }
}
