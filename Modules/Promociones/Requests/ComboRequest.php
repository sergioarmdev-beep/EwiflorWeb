<?php

namespace Modules\Promociones\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalogo\Models\Producto;

class ComboRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso ya se controla con el middleware role:GERENTE,ADMINISTRADOR en las rutas.
        return true;
    }

    public function rules(): array
    {
        $combo = $this->route('producto');
        $idCombo = $combo?->id_producto;

        return [
            'codigo' => ['required', 'string', 'max:30', Rule::unique('producto', 'codigo')->ignore($idCombo, 'id_producto')],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'categoria' => ['required', Rule::in(Producto::CATEGORIAS)],
            'id_marca' => ['required', 'exists:marca,id_marca'],
            'modelo' => ['nullable', 'string', 'max:60'],
            'modalidad' => ['required', Rule::in(['INVENTARIABLE', 'BAJO_PEDIDO'])],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'garantia_meses' => ['required', 'integer', 'min:0'],
            'es_publico' => ['nullable', 'boolean'],

            // Precio promocional del combo (se guarda en su única variante)
            'precio_usd' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],

            // Componentes del combo
            'componentes' => ['required', 'array', 'min:2', 'max:50'],
            'componentes.*.id_componente' => ['required', 'integer', 'exists:producto,id_producto'],
            'componentes.*.cantidad' => ['required', 'integer', 'min:1', 'max:999'],

            // Galería
            'imagenes' => ['nullable', 'array', 'max:10'],
            'imagenes.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'eliminar_imagenes' => ['nullable', 'array'],
            'eliminar_imagenes.*' => ['integer'],
            'imagen_principal' => ['nullable', 'integer'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $combo = $this->route('producto');
            $ids = array_map('intval', array_column($this->input('componentes', []), 'id_componente'));

            if (count($ids) !== count(array_unique($ids))) {
                $validator->errors()->add('componentes', 'No repitas el mismo producto dentro del combo; usa la cantidad.');
            }

            $productos = Producto::whereIn('id_producto', $ids)->get()->keyBy('id_producto');
            $nombres = [];

            foreach ($ids as $id) {
                if ($combo && $id === (int) $combo->id_producto) {
                    $validator->errors()->add('componentes', 'El combo no puede incluirse a sí mismo.');

                    continue;
                }

                $producto = $productos[$id] ?? null;

                if (! $producto) {
                    $validator->errors()->add('componentes', "El producto #{$id} no existe.");

                    continue;
                }

                if ($producto->tipo_producto === 'COMBO') {
                    $nombres[] = $producto->nombre;

                    continue;
                }

                if ($producto->estado !== 'ACTIVO') {
                    $validator->errors()->add('componentes', "«{$producto->nombre}» está inactivo.");
                }
            }

            if ($nombres !== []) {
                $validator->errors()->add('componentes', 'No se permiten combos anidados: '.implode(', ', $nombres).'.');
            }
        });
    }

    public function validated($key = null, $default = null)
    {
        $datos = parent::validated($key, $default);

        // Cuando se pide una clave concreta, parent ya devuelve ese valor.
        if ($key !== null) {
            return $datos;
        }

        $datos['descripcion'] = $datos['descripcion'] ?? null;
        $datos['modelo'] = $datos['modelo'] ?? null;

        return $datos;
    }

    public function attributes(): array
    {
        return [
            'precio_usd' => 'precio promocional del combo',
            'componentes.*.id_componente' => 'producto del combo',
            'componentes.*.cantidad' => 'cantidad del componente',
        ];
    }

    public function messages(): array
    {
        return [
            'componentes.min' => 'Un combo necesita al menos dos productos incluidos.',
            'componentes.required' => 'Agrega los productos que incluye el combo.',
            'codigo.unique' => 'Ya existe un producto o combo con ese código.',
            'imagenes.max' => 'Puede subir un máximo de 10 imágenes por vez.',
            'imagenes.*.max' => 'Cada imagen no puede superar los 2 MB.',
            'imagenes.*.image' => 'Los archivos deben ser imágenes.',
            'imagenes.*.mimes' => 'Las imágenes deben ser JPG, PNG o WEBP.',
        ];
    }
}
