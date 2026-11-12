<?php

namespace Modules\Catalogo\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Catalogo\Models\Producto;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso ya se controla con el middleware role:GERENTE,ADMINISTRADOR en las rutas.
        return true;
    }

    public function rules(): array
    {
        $producto = $this->route('producto');
        $idProducto = $producto?->id_producto;

        $reglas = [
            'codigo' => ['required', 'string', 'max:30', Rule::unique('producto', 'codigo')->ignore($idProducto, 'id_producto')],
            'nombre' => ['required', 'string', 'max:150'],
            'categoria' => ['required', Rule::in(Producto::CATEGORIAS)],
            'id_marca' => ['required', 'exists:marca,id_marca'],
            'tipo_producto' => ['required', Rule::in(Producto::TIPOS)],
            'modalidad' => ['required', Rule::in(['INVENTARIABLE', 'BAJO_PEDIDO'])],
            'modelo' => ['nullable', 'string', 'max:60'],
            'garantia_meses' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'descripcion' => ['nullable', 'string'],

            // Galería
            'imagenes' => ['nullable', 'array', 'max:10'],
            'imagenes.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'eliminar_imagenes' => ['nullable', 'array'],
            'eliminar_imagenes.*' => ['integer'],
            'imagen_principal' => ['nullable', 'integer'],

            // Variantes
            'variantes' => ['nullable', 'array', 'max:50'],
            'variantes.*.id_variante' => ['nullable', 'integer'],
            'variantes.*.color' => ['nullable', 'string', 'max:30'],
            'variantes.*.caracteristicas' => ['nullable', 'string', 'max:200'],
            'variantes.*.estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];

        // SKU: obligatorio, sin repetirse dentro del formulario ni en la tabla
        // (se ignora la propia variante cuando se está editando).
        foreach ((array) $this->input('variantes', []) as $i => $variante) {
            $reglas["variantes.$i.sku"] = [
                'required', 'string', 'max:40', 'distinct',
                Rule::unique('variante_producto', 'sku')->ignore($variante['id_variante'] ?? null, 'id_variante'),
            ];
        }

        return $reglas;
    }

    public function attributes(): array
    {
        $nombres = [
            'imagenes.*' => 'imagen',
            'imagenes' => 'imágenes',
        ];

        foreach ((array) $this->input('variantes', []) as $i => $_) {
            $n = $i + 1;
            $nombres["variantes.$i.sku"] = "SKU de la variante #$n";
            $nombres["variantes.$i.color"] = "color de la variante #$n";
        }

        return $nombres;
    }

    public function messages(): array
    {
        return [
            'imagenes.max' => 'Puede subir un máximo de 10 imágenes por vez.',
            'imagenes.*.max' => 'Cada imagen no puede superar los 2 MB.',
            'imagenes.*.image' => 'Los archivos deben ser imágenes.',
            'imagenes.*.mimes' => 'Las imágenes deben ser JPG, PNG o WEBP.',
            'variantes.*.sku.distinct' => 'Hay SKU repetidos en las variantes.',
        ];
    }
}
