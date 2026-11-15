<?php

namespace Modules\Sucursales\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Sucursales\Support\Catalogos;

class UpdateAlmacenRequest extends FormRequest
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
            'nombre' => [
                'required', 'string', 'max:80',
                Rule::unique('almacen', 'nombre')->ignore(
                    $this->route('almacen')?->id_almacen,
                    'id_almacen'
                ),
            ],
            'tamano' => ['required', 'string', Rule::in(Catalogos::TAMANOS_ALMACEN)],
            'id_sucursal' => ['required', 'integer', 'exists:sucursal,id_sucursal'],
        ];
    }
}
