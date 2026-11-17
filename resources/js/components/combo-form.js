/**
 * Componente Alpine.js: formulario de combos del módulo Promociones.
 *
 * Gestiona las filas dinámicas de productos incluidos en el combo: cada fila
 * es un select de producto + cantidad. Los inputs llevan nombres
 * `componentes[i][id_componente]` / `componentes[i][cantidad]` generados en
 * runtime, así que el envío sigue el formato que espera el backend.
 */
export function registerComboForm(Alpine) {
    Alpine.data('comboForm', ({ componentes = [], disponibles = [] }) => ({
        componentes: Array.isArray(componentes) ? componentes.map((c) => ({
            id_componente: String(c.id_componente ?? ''),
            cantidad: Number(c.cantidad ?? 1),
        })) : [],
        disponibles: Array.isArray(disponibles) ? disponibles : [],

        init() {
            if (this.componentes.length === 0) {
                this.agregar();
            }
        },

        agregar() {
            this.componentes.push({
                id_componente: '',
                cantidad: 1,
            });
        },

        quitar(i) {
            this.componentes.splice(i, 1);

            // Un combo siempre conserva al menos una fila visible para poder
            // seguir agregando.
            if (this.componentes.length === 0) {
                this.agregar();
            }
        },
    }));
}
