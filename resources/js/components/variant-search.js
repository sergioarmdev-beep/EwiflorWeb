/**
 * Componente Alpine.js: buscador/autocompletado de variantes de producto.
 *
 * Se usa dentro de las tablas de líneas de venta y cotización. Cada celda
 * monta un `x-data="variantSearch(items)"` que gestiona:
 *   - un input de texto para filtrar por SKU, nombre, marca, color o código;
 *   - un desplegable con los resultados (limitado a 60);
 *   - un input oculto con el `id_variante` real que se envía al formulario.
 *
 * El desplegable usa `position: fixed` calculado en runtime para poder
 * desbordar las tablas con `overflow-x-auto` sin que lo recorten.
 *
 * El input oculto es el que dispara el evento `change` para que el JavaScript
 * existente de la vista recalcule precio, garantía y subtotal.
 */
const ANCHO_MINIMO = 260;
const MARGEN = 8;
const ALTO_MAXIMO = 256;

export function registerVariantSearch(Alpine) {
    Alpine.data('variantSearch', (items = []) => ({
        items: Array.isArray(items) ? items : [],
        query: '',
        open: false,
        highlighted: 0,
        selectedId: '',

        init() {
            // Los `x-ref` se registran después de procesar el elemento, por eso
            // la inicialización se aplaza al siguiente tick.
            this.$nextTick(() => {
                const hidden = this.$refs.hidden;

                this.selectedId = hidden?.value ? String(hidden.value) : '';
                this.query = this.labelFor(this.selectedId);
                this.highlighted = 0;
            });

            // Mantener el desplegable anclado al input mientras se desplaza la página.
            this._reposicionar = () => {
                if (this.open) {
                    this.$forceUpdate();
                }
            };

            window.addEventListener('scroll', this._reposicionar, true);
            window.addEventListener('resize', this._reposicionar);
        },

        destroy() {
            window.removeEventListener('scroll', this._reposicionar, true);
            window.removeEventListener('resize', this._reposicionar);
        },

        /** Resultados filtrados por el texto escrito. */
        get resultados() {
            const q = this.query.trim().toLowerCase();

            const lista = q
                ? this.items.filter((item) => this.haystack(item).includes(q))
                : this.items;

            return lista.slice(0, 60);
        },

        /**
         * Posición del desplegable respecto a la ventana (`position: fixed`).
         * Se coloca debajo del input; si no hay espacio, se abre hacia arriba.
         */
        estiloDesplegable() {
            const input = this.$refs.input;

            if (!input) {
                return {};
            }

            const rect = input.getBoundingClientRect();
            const ancho = Math.max(rect.width, ANCHO_MINIMO);

            let left = rect.left;
            if (left + ancho > window.innerWidth - MARGEN) {
                left = Math.max(MARGEN, window.innerWidth - ancho - MARGEN);
            }

            const espacioAbajo = window.innerHeight - rect.bottom;
            const altoEstimado = Math.min(ALTO_MAXIMO, this.resultados.length * 52 + 40);
            const abrirArriba = espacioAbajo < altoEstimado && rect.top > altoEstimado;

            return {
                top: (abrirArriba ? rect.top - 4 : rect.bottom + 4) + 'px',
                left: left + 'px',
                width: ancho + 'px',
                transform: abrirArriba ? 'translateY(-100%)' : 'none',
            };
        },

        /** Texto indexable de una variante. */
        haystack(item) {
            return [item.sku, item.codigo, item.nombre, item.marca, item.color]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();
        },

        /** Etiqueta legible de una variante por id. */
        labelFor(id) {
            if (!id) {
                return '';
            }

            const item = this.items.find((i) => String(i.id) === String(id));

            if (!item) {
                return '';
            }

            const extra = item.color ? ` (${item.color})` : '';
            const promo = this.promoEtiqueta(item);

            return `${item.sku} — ${item.nombre}${extra}${promo ? ` · ${promo}` : ''}`;
        },

        /** Texto del chip de promoción («−15 %», «USD −10») de la variante. */
        promoEtiqueta(item) {
            const promo = item?.promo;

            if (!promo || !promo.valor) {
                return '';
            }

            return promo.tipo === 'USD'
                ? `USD −${Number(promo.valor).toFixed(2)}`
                : `−${Number(promo.pct ?? promo.valor).toFixed(2).replace(/\.?0+$/, '')} %`;
        },

        /** Selecciona una variante y notifica el cambio al formulario. */
        elegir(item) {
            if (!item) {
                return;
            }

            const hidden = this.$refs.hidden;

            hidden.value = item.id;
            this.selectedId = String(item.id);
            this.query = this.labelFor(item.id);
            this.open = false;
            this.highlighted = 0;

            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        },

        /** Permite limpiar la selección con el botón ✕. */
        limpiar() {
            const hidden = this.$refs.hidden;

            hidden.value = '';
            this.selectedId = '';
            this.query = '';
            this.open = false;
            this.highlighted = 0;

            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }));
}
