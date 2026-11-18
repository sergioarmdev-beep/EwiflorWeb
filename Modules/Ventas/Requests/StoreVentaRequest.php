<?php

namespace Modules\Ventas\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Modules\Sucursales\Support\AlcanceDocumentos;

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
            'modo' => ['required', Rule::in(['directa', 'cotizacion'])],
            'id_almacen' => ['required', 'integer', $this->reglaAlmacen()],

            // Modo cotización
            'id_cotizacion' => ['required_if:modo,cotizacion', 'integer', 'exists:cotizacion,id_cotizacion'],

            // Modo directa
            'id_cliente' => ['required_if:modo,directa', 'integer', 'exists:cliente,id_cliente'],
            'lineas' => ['required_if:modo,directa', 'array', 'min:1'],
            'lineas.*.id_variante' => [
                'required_if:modo,directa',
                'integer',
                'exists:variante_producto,id_variante',
                'distinct',
            ],
            'lineas.*.cantidad' => ['required_if:modo,directa', 'integer', 'min:1'],
            // El precio se toma del catálogo; si llega, igual se ignora en el servicio.
            'lineas.*.precio_unitario_usd' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'lineas.*.descuento_tipo' => ['nullable', Rule::in(['PCT', 'USD'])],
            'lineas.*.descuento_valor' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'lineas.*.garantia_meses' => ['nullable', 'integer', 'min:0', 'max:1200'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_almacen.exists' => $this->sinSucursal()
                ? 'Tu usuario no tiene sucursal asignada: no puedes registrar ventas.'
                : 'El almacén indicado no existe o no está activo.',
        ];
    }

    /**
     * Almacén ACTIVO y, para quien no es Gerente, de su propia sucursal.
     * Es solo validación de entrada: la autoridad sigue siendo VentaService
     * y la guarda de la función PostgreSQL registrar_venta().
     */
    private function reglaAlmacen(): Exists
    {
        $rule = Rule::exists('almacen', 'id_almacen')->where('estado', 'ACTIVO');

        $user = $this->user();
        if ($user === null || AlcanceDocumentos::esGerente($user)) {
            return $rule;
        }

        // Sin sucursal asignada no existe ningún almacén válido (-1 nunca existe).
        $rule->where('id_sucursal', AlcanceDocumentos::sucursalDelUsuario($user) ?? -1);

        return $rule;
    }

    private function sinSucursal(): bool
    {
        $user = $this->user();

        return $user !== null
            && ! AlcanceDocumentos::esGerente($user)
            && AlcanceDocumentos::sucursalDelUsuario($user) === null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('modo') === 'directa') {
            $lineas = collect($this->input('lineas', []))->map(function ($l) {
                $l['descuento_tipo'] = $l['descuento_tipo'] ?? 'PCT';
                $l['descuento_valor'] = $l['descuento_valor'] ?? ($l['descuento_pct'] ?? 0);

                return $l;
            })->all();
            $this->merge(['lineas' => $lineas]);
        }
    }
}
