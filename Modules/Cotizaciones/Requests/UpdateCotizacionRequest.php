<?php

namespace Modules\Cotizaciones\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCotizacionRequest extends FormRequest
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
            'id_cliente'   => ['required', 'integer', 'exists:cliente,id_cliente'],
            'vigencia_dias' => ['required', 'integer', 'min:1', 'max:90'],
            'observacion'  => ['nullable', 'string', 'max:200'],

            'lineas'                          => ['required', 'array', 'min:1'],
            'lineas.*.id_variante'            => [
                'required',
                'integer',
                'exists:variante_producto,id_variante',
                'distinct'
            ],
            'lineas.*.cantidad'               => ['required', 'integer', 'min:1'],
            'lineas.*.precio_unitario_usd'    => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'lineas.*.descuento_pct'          => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $lineas = collect($this->input('lineas', []))->map(function ($l) {
            $l['descuento_pct'] = $l['descuento_pct'] ?? 0;
            return $l;
        })->all();

        $this->merge(['lineas' => $lineas]);
    }
}
