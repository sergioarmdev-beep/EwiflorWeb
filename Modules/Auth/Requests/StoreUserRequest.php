<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        return [
            'nombre_completo' => ['required', 'string', 'max:100'],
            'nombre_usuario'  => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('usuario', 'nombre_usuario'),
            ],
            'correo'          => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('usuario', 'correo'),
            ],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
            'nombre_rol'      => ['required', Rule::in(['GERENTE', 'ADMINISTRADOR', 'VENDEDOR', 'SERVICIO_TECNICO'])],
            'id_sucursal'     => ['nullable', 'integer', 'exists:sucursal,id_sucursal'],
            'porcentaje_comision_pct' => ['required', 'numeric', 'between:0,100'],
            'estado'          => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_usuario.unique' => 'Ese nombre de usuario ya está registrado.',
            'correo.unique'         => 'Ese correo ya está registrado.',
            'password.confirmed'    => 'La confirmación de contraseña no coincide.',
            'password.min'          => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
