<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Models\User;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarUsuarios() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre_usuario' => strtolower(trim((string) $this->nombre_usuario)),
            'correo'         => $this->correo ? strtolower(trim($this->correo)) : null,
        ]);
    }

    public function rules(): array
    {
        /** @var User $usuario */
        $usuario = $this->route('usuario');

        return [
            'nombre_completo' => ['required', 'string', 'max:100'],
            'nombre_usuario'  => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('usuario', 'nombre_usuario')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            'correo'          => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('usuario', 'correo')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            'password'        => ['nullable', 'string', 'min:8', 'confirmed'],
            'nombre_rol'      => ['required', Rule::in(['GERENTE', 'ADMINISTRADOR', 'VENDEDOR', 'SERVICIO_TECNICO'])],
            'id_sucursal'     => ['nullable', 'integer', 'exists:sucursal,id_sucursal'],
            'porcentaje_comision_pct' => ['required', 'numeric', 'between:0,100'],
            'estado'          => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }
}
