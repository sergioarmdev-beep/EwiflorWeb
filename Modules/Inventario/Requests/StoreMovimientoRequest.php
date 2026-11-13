<?php

namespace Modules\Inventario\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && in_array($user->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true);
    }

    public function rules(): array
    {
        $tipo = $this->input('tipo');

        return [
            'tipo' => ['required', Rule::in(['ENTRADA', 'SALIDA', 'AJUSTE', 'TRANSFERENCIA'])],
            'motivo' => ['required', 'string', 'max:200'],
            'id_variante' => ['required', 'integer', 'exists:variante_producto,id_variante'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:999999'],

            // Almacén de origen: requerido en SALIDA, AJUSTE y TRANSFERENCIA
            'id_almacen_origen' => [
                Rule::requiredIf(in_array($tipo, ['SALIDA', 'AJUSTE', 'TRANSFERENCIA'], true)),
                'nullable',
                'integer',
                'exists:almacen,id_almacen',
            ],

            // Almacén de destino: requerido en ENTRADA y TRANSFERENCIA
            'id_almacen_destino' => [
                Rule::requiredIf(in_array($tipo, ['ENTRADA', 'TRANSFERENCIA'], true)),
                'nullable',
                'integer',
                'exists:almacen,id_almacen',
                Rule::notIn([$this->input('id_almacen_origen')]),
            ],

            // Sentido: requerido solo en AJUSTE
            'sentido' => [
                Rule::requiredIf($tipo === 'AJUSTE'),
                'nullable',
                Rule::in(['INCREMENTO', 'DECREMENTO']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_almacen_origen.required'  => 'Debes indicar el almacén de origen.',
            'id_almacen_destino.required' => 'Debes indicar el almacén de destino.',
            'id_almacen_destino.not_in'   => 'El origen y el destino no pueden ser el mismo almacén.',
            'sentido.required'            => 'Los ajustes requieren indicar el sentido (incremento/decremento).',
        ];
    }

    /**
     * Normaliza a null los campos que no aplican al tipo elegido.
     */
    protected function passedValidation(): void
    {
        $tipo = $this->input('tipo');

        $this->merge([
            'id_almacen_origen'  => in_array($tipo, ['SALIDA', 'AJUSTE', 'TRANSFERENCIA'], true)
                ? $this->input('id_almacen_origen') : null,
            'id_almacen_destino' => in_array($tipo, ['ENTRADA', 'TRANSFERENCIA'], true)
                ? $this->input('id_almacen_destino') : null,
            'sentido'            => $tipo === 'AJUSTE' ? $this->input('sentido') : null,
        ]);
    }
}
