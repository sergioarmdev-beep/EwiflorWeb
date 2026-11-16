@php
$isEdit = $cotizacion->exists;
$lineas = old('lineas', $lineas ?? []);
$nextIdx = count($lineas);
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium">Cliente *</label>
        <select name="id_cliente" class="w-full border rounded px-3 py-2" required>
            <option value="">— Selecciona un cliente —</option>
            @foreach ($clientes as $cli)
            <option value="{{ $cli->id_cliente }}"
                @selected((string) old('id_cliente', $cotizacion->id_cliente) === (string) $cli->id_cliente)>
                {{ $cli->nombre }} — {{ $cli->telefono }}
            </option>
            @endforeach
        </select>
        @error('id_cliente') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium">Vigencia (días) *</label>
        <input type="number" name="vigencia_dias" min="1" max="90" required
            value="{{ old('vigencia_dias', $cotizacion->vigencia_dias ?? 14) }}"
            class="w-full border rounded px-3 py-2">
        @error('vigencia_dias') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-3">
        <label class="block text-sm font-medium">Observación</label>
        <textarea name="observacion" rows="2" maxlength="200"
            class="w-full border rounded px-3 py-2">{{ old('observacion', $cotizacion->observacion) }}</textarea>
    </div>
</div>

<h2 class="font-semibold mb-2">Líneas de la cotización</h2>

@if ($errors->has('lineas'))
<div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">{{ $errors->first('lineas') }}</div>
@endif

<div class="overflow-x-auto rounded-lg bg-white shadow">
<table class="w-full text-sm">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-3 py-2 text-left min-w-[240px]">Variante</th>
            <th class="px-3 py-2 text-right w-20">Cantidad</th>
            <th class="px-3 py-2 text-right w-28">Precio USD</th>
            <th class="px-3 py-2 text-right w-20">Garantía (meses)</th>
            <th class="px-3 py-2 text-right w-24">Tipo desc.</th>
            <th class="px-3 py-2 text-right w-28">Descuento</th>
            <th class="px-3 py-2 text-right w-28">Subtotal USD</th>
            <th class="w-10"></th>
        </tr>
    </thead>
    <tbody id="lineas-body" data-next="{{ $nextIdx }}">
        @foreach ($lineas as $i => $l)
        <tr>
            <td class="px-3 py-2">
                <x-variant-search
                    name="lineas[{{ $i }}][id_variante]"
                    :items="$variantesBusqueda"
                    :value="old('lineas.'.$i.'.id_variante', $l['id_variante'] ?? '')" />
            </td>
            <td class="px-3 py-2">
                <input type="number" name="lineas[{{ $i }}][cantidad]" min="1" required
                    value="{{ $l['cantidad'] ?? 1 }}"
                    class="w-full border rounded px-2 py-1 text-right">
            </td>
            <td class="px-3 py-2">
                <input type="number" readonly tabindex="-1" placeholder="—"
                    name="lineas[{{ $i }}][precio_unitario_usd]"
                    step="0.01" value="{{ $l['precio_unitario_usd'] ?? '' }}"
                    class="w-full border rounded px-2 py-1 text-right bg-gray-100 text-gray-600 cursor-not-allowed">
            </td>
            <td class="px-3 py-2"><input type="number" name="lineas[{{ $i }}][garantia_meses]" min="0" value="{{ $l['garantia_meses'] ?? $garantias[$l['id_variante']] ?? 0 }}" class="w-full border rounded px-2 py-1 text-right" placeholder="0"></td>
            <td class="px-3 py-2">
                <select name="lineas[{{ $i }}][descuento_tipo]" class="w-full border rounded px-2 py-1">
                    <option value="PCT" @selected(($l['descuento_tipo'] ?? 'PCT') === 'PCT')>%</option>
                    <option value="USD" @selected(($l['descuento_tipo'] ?? '') === 'USD')>USD</option>
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="number" name="lineas[{{ $i }}][descuento_valor]"
                    step="0.01" min="0"
                    value="{{ $l['descuento_valor'] ?? ($l['descuento_pct'] ?? 0) }}"
                    class="w-full border rounded px-2 py-1 text-right">
            </td>
            <td class="px-3 py-2 text-right text-gray-600"><span data-subtotal>0.00</span></td>
            <td class="px-3 py-2">
                <button type="button" class="remove text-red-600" title="Quitar">✕</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>

<p class="text-xs text-gray-500 mt-2">
    El precio se toma del catálogo y no puede editarse. La garantía por defecto viene del catálogo
    y puede editarse por línea. Solo se permite un descuento por línea,
    en porcentaje o en monto USD sobre el total de la línea.
</p>

<div class="mt-2">
    <button type="button" id="add-linea"
        class="text-blue-600 hover:underline text-sm">+ Agregar línea</button>
</div>

<template id="linea-tpl">
    <tr>
        <td class="px-3 py-2">
            <x-variant-search
                name=""
                :items="$variantesBusqueda"
                value="" />
        </td>
        <td class="px-3 py-2">
            <input type="number" data-name="cantidad" min="1" value="1" required
                class="w-full border rounded px-2 py-1 text-right">
        </td>
        <td class="px-3 py-2">
            <input type="number" data-name="precio_unitario_usd" readonly tabindex="-1" placeholder="—"
                step="0.01"
                class="w-full border rounded px-2 py-1 text-right bg-gray-100 text-gray-600 cursor-not-allowed">
        </td>
        <td class="px-3 py-2"><input type="number" data-name="garantia_meses" min="0" value="0" class="w-full border rounded px-2 py-1 text-right" placeholder="0"></td>
        <td class="px-3 py-2">
            <select data-name="descuento_tipo" class="w-full border rounded px-2 py-1">
                <option value="PCT">%</option>
                <option value="USD">USD</option>
            </select>
        </td>
        <td class="px-3 py-2">
            <input type="number" data-name="descuento_valor" step="0.01" min="0" value="0"
                class="w-full border rounded px-2 py-1 text-right">
        </td>
        <td class="px-3 py-2 text-right text-gray-600"><span data-subtotal>0.00</span></td>
        <td class="px-3 py-2">
            <button type="button" class="remove text-red-600" title="Quitar">✕</button>
        </td>
    </tr>
</template>

<div class="mt-6 flex gap-2">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
        {{ $isEdit ? 'Actualizar cotización' : 'Emitir cotización' }}
    </button>
    <a href="{{ route('cotizaciones.index') }}" class="border rounded px-4 py-2">Cancelar</a>
</div>

<script>
    (function() {
        const tbody = document.getElementById('lineas-body');
        const tpl = document.getElementById('linea-tpl');
        if (!tbody || !tpl) return;

        const precios = @json($precios ?? []);
        const garantias = @json($garantias ?? []);
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

        function addRow() {
            const frag = tpl.content.cloneNode(true);
            const tr = frag.querySelector('tr');
            tr.querySelectorAll('[data-name]').forEach(el => {
                el.name = 'lineas[' + idx + '][' + el.dataset.name + ']';
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

        tbody.querySelectorAll('tr').forEach(tr => {
            bindRemove(tr);
            aplicarPrecio(tr);
        });

        if (tbody.children.length === 0) addRow();
    })();
</script>