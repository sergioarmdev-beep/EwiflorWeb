/**
 * Componente Alpine.js: formulario de promociones del módulo Promociones.
 *
 * Gestiona:
 *   - el tipo de descuento (PCT o USD) y su valor, para previsualizar el
 *     precio final de cada variante;
 *   - la selección de variantes promocionadas: un picker de producto que
 *     muestra las variantes del producto elegido como checkboxes, con un
 *     filtro por SKU, y chips resumen de lo seleccionado.
 *
 * Los ids seleccionados viajan al servidor en los checkboxes con
 * `name="variantes[]"`, así que el formulario funciona sin repoblar nada.
 */
export function registerPromocionForm(Alpine) {
    Alpine.data('promocionForm', ({ items = [], seleccionadas = [], tipo = 'PCT', valor = '' }) => ({
        items: Array.isArray(items) ? items : [],
        seleccionadas: Array.isArray(seleccionadas) ? seleccionadas.map(Number) : [],
        tipo,
        valor,
        productoSel: '',
        busqueda: '',

        /** Productos únicos de las variantes indexables. */
        productos() {
            const vistos = new Map();

            for (const v of this.items) {
                if (!vistos.has(v.id_producto)) {
                    vistos.set(v.id_producto, { id: v.id_producto, nombre: v.producto });
                }
            }

            return [...vistos.values()].sort((a, b) => a.nombre.localeCompare(b.nombre));
        },

        /** Variantes visibles: del producto elegido y que coincidan con el filtro. */
        variantesVisibles() {
            const q = this.busqueda.toLowerCase().trim();

            return this.items.filter((v) => {
                if (this.productoSel !== '' && String(v.id_producto) !== this.productoSel) {
                    return false;
                }

                return q === '' || `${v.sku} ${v.color ?? ''}`.toLowerCase().includes(q);
            });
        },

        /** Agrega o quita una variante de la selección. */
        alternar(idVariante) {
            const id = Number(idVariante);

            this.seleccionadas = this.seleccionadas.includes(id)
                ? this.seleccionadas.filter((v) => v !== id)
                : [...this.seleccionadas, id];
        },

        /**
         * Precio final de la variante con la promo actual (o null si no se
         * puede calcular: sin precio o sin valor).
         */
        precioFinal(v) {
            const precio = Number(v.precio);
            const valor = Number(this.valor);

            if (!precio || !valor) {
                return null;
            }

            return this.tipo === 'USD'
                ? Math.max(0, precio - valor)
                : Math.max(0, precio - (precio * valor) / 100);
        },
    }));
}
