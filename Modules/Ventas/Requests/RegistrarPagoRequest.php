<?php

namespace Modules\Ventas\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Ventas\Models\Pago;

class RegistrarPagoRequest extends FormRequest
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
            'forma_pago' => ['required', Rule::in(Pago::FORMAS)],
            'monto_bs'   => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'referencia' => ['nullable', 'string', 'max:60'],
        ];
    }
}
