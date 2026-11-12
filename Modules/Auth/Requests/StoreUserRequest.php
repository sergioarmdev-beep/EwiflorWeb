<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Support\ReglasContrasena;

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
            'password'        => array_merge(ReglasContrasena::reglas(true), ['confirmed']),
            'nombre_rol'      => ['required', Rule::in($this->user()->rolesAsignables())],
            'id_sucursal'     => ['nullable', 'integer', 'exists:sucursal,id_sucursal'],
            'porcentaje_comision_pct' => ['required', 'numeric', 'between:0,100'],
            'estado'          => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
            // Si el usuario se crea a partir de una solicitud de cuenta pendiente
            'id_solicitud'    => [
                'nullable',
                'integer',
                Rule::exists('solicitud_cuenta', 'id_solicitud')->where('estado', 'PENDIENTE'),
            ],
        ];
    }

    public function messages(): array
    {
        return ReglasContrasena::mensajes() + [
            'nombre_usuario.unique' => 'Ese nombre de usuario ya está registrado.',
            'correo.unique'         => 'Ese correo ya está registrado.',
            'nombre_rol.in'         => 'El rol seleccionado no está permitido para tu cuenta.',
            'id_solicitud.exists'   => 'La solicitud ya fue procesada o no existe.',
        ];
    }
}
