<?php

namespace Modules\ServicioTecnico\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La web pública se autentica con la sesión propia del cliente
        // (middleware sesion.cliente.publica), no con Auth.
        if ($this->routeIs('serviciotecnico.publico.guardar')) {
            return true;
        }

        $user = $this->user();

        return $user !== null && in_array(
            $user->nombre_rol,
            ['VENDEDOR', 'GERENTE', 'ADMINISTRADOR'],
            true
        );
    }

    public function rules(): array
    {
        $reglas = [
            'descripcion_falla' => ['required', 'string', 'min:5', 'max:1000'],
            'id_garantia' => ['nullable', 'integer', 'exists:garantia,id_garantia'],
        ];

        // En el panel interno el cliente se resuelve por nombre + celular;
        // en la web pública ya viene de la sesión del cliente.
        if ($this->routeIs('serviciotecnico.nueva.store')) {
            $reglas['nombre_cliente'] = ['required', 'string', 'min:2', 'max:100'];
            $reglas['telefono_cliente'] = ['required', 'string', 'min:7', 'max:20'];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'descripcion_falla.required' => 'Debes describir la falla del equipo.',
            'descripcion_falla.min' => 'La descripción debe tener al menos 5 caracteres.',
            'nombre_cliente.required' => 'Debes indicar el nombre del cliente.',
            'telefono_cliente.required' => 'Debes indicar el celular del cliente.',
            'id_garantia.exists' => 'La garantía seleccionada no existe.',
        ];
    }
}
