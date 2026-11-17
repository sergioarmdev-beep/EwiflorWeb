<?php

namespace Modules\Promociones\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Promociones\Models\Promocion;

class PromocionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso ya se controla con el middleware role:GERENTE,ADMINISTRADOR en las rutas.
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:250'],
            'tipo_descuento' => ['required', Rule::in(Promocion::TIPOS)],
            'valor_descuento' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', Rule::in(Promocion::ESTADOS)],
            'variantes' => ['required', 'array', 'min:1', 'max:200'],
            'variantes.*' => ['integer', 'distinct', 'exists:variante_producto,id_variante'],
        ];
    }

    public function withValidator($validator)
    {
        // Un porcentaje de promoción nunca puede superar 100 %.
        $validator->after(function ($validator) {
            if ($this->input('tipo_descuento') === 'PCT'
                && (float) $this->input('valor_descuento') > 100) {
                $validator->errors()->add('valor_descuento', 'El porcentaje de descuento no puede superar 100 %.');
            }
        });
    }

    public function validated($key = null, $default = null)
    {
        $datos = parent::validated($key, $default);

        // Cuando se pide una clave concreta, parent ya devuelve ese valor.
        if ($key !== null) {
            return $datos;
        }

        // Sin fecha de fin, la promoción queda abierta.
        $datos['fecha_fin'] = $datos['fecha_fin'] ?? null;
        $datos['descripcion'] = $datos['descripcion'] ?? null;

        return $datos;
    }

    public function attributes(): array
    {
        return [
            'variantes' => 'variantes promocionadas',
            'variantes.*' => 'variante promocionada',
            'valor_descuento' => 'valor del descuento',
            'tipo_descuento' => 'tipo de descuento',
        ];
    }

    public function messages(): array
    {
        return [
            'variantes.required' => 'Selecciona al menos una variante para la promoción.',
            'variantes.*.distinct' => 'Hay variantes repetidas en la selección.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ];
    }
}
