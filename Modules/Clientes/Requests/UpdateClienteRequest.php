<?php

namespace Modules\Clientes\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Clientes\Models\Cliente;

class StoreClienteRequest extends FormRequest
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
            'nombre'       => ['required', 'string', 'max:120'],
            'telefono'     => ['required', 'string', 'max:20'],
            'correo'       => ['nullable', 'email', 'max:100'],
            'tipo_cliente' => ['nullable', Rule::in(Cliente::TIPOS)],
            'es_mayorista' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'es_mayorista' => $this->boolean('es_mayorista'),
            'correo'       => $this->filled('correo')
                ? strtolower(trim($this->input('correo')))
                : null,
        ]);
    }
}
