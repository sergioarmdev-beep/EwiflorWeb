<?php

namespace Modules\Sucursales\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Sucursales\Support\Catalogos;

class UpdateSucursalRequest extends FormRequest
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
                Rule::unique('sucursal', 'nombre')->ignore(
                    $this->route('sucursal')?->id_sucursal,
                    'id_sucursal'
                ),
            ],
            'departamento' => ['required', 'string', Rule::in(Catalogos::DEPARTAMENTOS)],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ];
    }
}
