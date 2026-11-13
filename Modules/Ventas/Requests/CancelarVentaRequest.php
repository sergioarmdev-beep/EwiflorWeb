<?php

namespace Modules\Ventas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelarVentaRequest extends FormRequest
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
        return ['motivo' => ['required', 'string', 'min:5', 'max:200']];
    }
}
