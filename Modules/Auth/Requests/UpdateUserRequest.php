<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Models\User;
use Modules\Auth\Support\Celular;
use Modules\Auth\Support\ReglasContrasena;

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
            'correo' => $this->correo ? strtolower(trim($this->correo)) : null,
            'celular' => trim((string) $this->celular) === '' ? null : Celular::normalizar($this->celular),
        ]);
    }

    public function rules(): array
    {
        /** @var User $usuario */
        $usuario = $this->route('usuario');

        return [
            'nombre_completo' => ['required', 'string', 'max:100'],
            'nombre_usuario' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('usuario', 'nombre_usuario')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            // Correo y celular son obligatorios: sin ellos la cuenta no podría
            // usar «Olvidé mi contraseña».
            'correo' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuario', 'correo')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            'celular' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+()\-\s]{7,20}$/',
            ],
            'password' => array_merge(ReglasContrasena::reglas(false), ['confirmed']),
            'nombre_rol' => ['required', Rule::in($this->user()->rolesAsignables())],
            'id_sucursal' => ['nullable', 'integer', 'exists:sucursal,id_sucursal'],
            'porcentaje_comision_pct' => ['required', 'numeric', 'between:0,100'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    public function messages(): array
    {
        return ReglasContrasena::mensajes() + [
            'nombre_usuario.unique' => 'Ese nombre de usuario ya está registrado.',
            'correo.required' => 'El correo es obligatorio (se usa para «Olvidé mi contraseña»).',
            'correo.unique' => 'Ese correo ya está registrado.',
            'celular.required' => 'El celular es obligatorio (se usa para «Olvidé mi contraseña»).',
            'celular.regex' => 'Ingresa un celular válido (solo dígitos, +, espacios o guiones).',
            'nombre_rol.in' => 'El rol seleccionado no está permitido para tu cuenta.',
        ];
    }
}
