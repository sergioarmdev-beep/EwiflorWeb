{{--
Lógica compartida del editor de líneas de ventas y cotizaciones.
Se incluye con @include('components.lineas-documento-scripts', [...])
Dentro de @push('scripts') en Ventas y directamente en Cotizaciones.
--}}
<script>
    (function () {
        const tbody = document.getElementById('lineas-body');
        const tpl = document.getElementById('linea-tpl');
        if (!tbody || !tpl) return;

        const precios = @json($precios ?? []);
        const garantias = @json($garantias ?? []);
        const promociones = @json($promociones ?? []);
        const puedeEditarDescuento = @json($puedeEditarDescuento ?? true);
        let idx = Number(tbody.dataset.next || 0);

        const numero = (valor) => {
            const n = parseFloat(valor);
            return Number.isFinite(n) ? n : 0;
        };

        const campo = (tr, nombre) =>
            tr.querySelector(`[name$="[${nombre}]"], [data-name="${nombre}"]`);

        function bindRemove(tr) {
            const btn = tr.querySelector('.remove');
            if (btn) btn.addEventListener('click', () => tr.remove());
        }

        function precioDe(tr) {
            const id = campo(tr, 'id_variante')?.value;
            if (!id) return null;
            const precio = Number(precios[id] ?? 0);
            return precio > 0 ? precio : null;
        }

        function recalcular(tr) {
            const precio = precioDe(tr);
            const cantidad = Math.max(1, parseInt(campo(tr, 'cantidad')?.value || '1', 10));
            const tipo = campo(tr, 'descuento_tipo')?.value || 'PCT';
            const inputDescuento = campo(tr, 'descuento_valor');
            const descuento = Math.max(0, numero(inputDescuento?.value));

            let subtotal = 0;
            if (precio) {
                const bruto = precio * cantidad;
                if (tipo === 'USD') {
                    subtotal = Math.max(0, bruto - descuento);
                    if (inputDescuento) inputDescuento.max = bruto.toFixed(2);
                } else {
                    subtotal = bruto * (1 - Math.min(descuento, 100) / 100);
                    if (inputDescuento) inputDescuento.removeAttribute('max');
                }
            }

            const etiqueta = tr.querySelector('[data-subtotal]');
            if (etiqueta) etiqueta.textContent = subtotal.toFixed(2);
        }

        function aplicarPrecio(tr) {
            const input = campo(tr, 'precio_unitario_usd');
            if (!input) return;
            const precio = precioDe(tr);
            input.value = precio ? precio.toFixed(2) : '';
            input.title = precio ? '' : 'Esta variante no tiene precio de lista';
            recalcular(tr);
        }

        /**
         * Al cambiar de variante se propone la garantía por defecto del catálogo.
         * Solo se hace en el evento de cambio (nunca al cargar la página), para no
         * pisar una garantía editada previamente por el usuario.
         */
        function aplicarGarantia(tr) {
            const id = campo(tr, 'id_variante')?.value;
            const input = campo(tr, 'garantia_meses');
            if (!id || !input) return;
            input.value = Number(garantias[id] ?? 0);
        }

        /**
         * Al cambiar de variante se precarga el descuento de la promoción
         * vigente. Para VENDEDOR los controles quedan bloqueados: solo puede
         * aplicar el descuento de la promo (o ninguno), nunca más.
         */
        function aplicarPromo(tr) {
            const id = campo(tr, 'id_variante')?.value;
            const tipoSel = campo(tr, 'descuento_tipo');
            const inputDesc = campo(tr, 'descuento_valor');
            if (!id || !tipoSel || !inputDesc) return;

            const promo = promociones[id];

            if (promo) {
                tipoSel.value = promo.tipo;
                inputDesc.value = promo.valor;
                inputDesc.title = `Promoción vigente: ${promo.nombre}`;
            } else {
                inputDesc.value = 0;
                inputDesc.title = '';
            }

            if (!puedeEditarDescuento) {
                inputDesc.readOnly = true;
                [...tipoSel.options].forEach((o) => {
                    o.disabled = !promo || o.value !== promo.tipo;
                });
            } else {
                inputDesc.readOnly = false;
                [...tipoSel.options].forEach((o) => {
                    o.disabled = false;
                });
            }

            recalcular(tr);
        }

        function addRow() {
            const tr = tpl.content.cloneNode(true).querySelector('tr');
            tr.querySelectorAll('[data-name]').forEach((el) => {
                el.name = `lineas[${idx}][${el.dataset.name}]`;
            });
            bindRemove(tr);
            tbody.appendChild(tr);
            idx++;
            aplicarPrecio(tr);
        }

        document.getElementById('add-linea')?.addEventListener('click', addRow);

        tbody.addEventListener('change', (e) => {
            const tr = e.target.closest('tr');
            if (!tr) return;
            if (e.target.matches('[name$="[id_variante]"], [data-name="id_variante"]')) {
                aplicarPrecio(tr);
                aplicarGarantia(tr);
                aplicarPromo(tr);
            } else {
                recalcular(tr);
            }
        });

        tbody.addEventListener('input', (e) => {
            const tr = e.target.closest('tr');
            if (!tr) return;
            if (e.target.matches('[name$="[cantidad]"], [name$="[descuento_valor]"], [data-name="cantidad"], [data-name="descuento_valor"]')) {
                recalcular(tr);
            }
        });

        tbody.querySelectorAll('tr').forEach((tr) => {
            bindRemove(tr);
            aplicarPrecio(tr);
        });

        if (tbody.children.length === 0) addRow();
    })();
</script>
