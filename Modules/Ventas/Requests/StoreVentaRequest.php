<?php

namespace Modules\Ventas\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array(
            $this->user()->nombre_rol,
            ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'],
            true
        );
    }

    public function rules(): array
    {
        return [
            'modo'         => ['required', Rule::in(['directa', 'cotizacion'])],
            'id_almacen'   => ['required', 'integer', 'exists:almacen,id_almacen'],

            // Modo cotización
            'id_cotizacion' => ['required_if:modo,cotizacion', 'integer', 'exists:cotizacion,id_cotizacion'],

            // Modo directa
            'id_cliente'   => ['required_if:modo,directa', 'integer', 'exists:cliente,id_cliente'],
            'lineas'                          => ['required_if:modo,directa', 'array', 'min:1'],
            'lineas.*.id_variante'            => [
                'required_if:modo,directa',
                'integer',
                'exists:variante_producto,id_variante',
                'distinct'
            ],
            'lineas.*.cantidad'               => ['required_if:modo,directa', 'integer', 'min:1'],
            'lineas.*.precio_unitario_usd'    => ['required_if:modo,directa', 'numeric', 'min:0', 'max:9999999.99'],
            'lineas.*.descuento_pct'          => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('modo') === 'directa') {
            $lineas = collect($this->input('lineas', []))->map(function ($l) {
                $l['descuento_pct'] = $l['descuento_pct'] ?? 0;
                return $l;
            })->all();
            $this->merge(['lineas' => $lineas]);
        }
    }
}
