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

<table class="w-full text-sm bg-white shadow rounded overflow-hidden">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-3 py-2 text-left">Variante</th>
            <th class="px-3 py-2 text-right w-24">Cantidad</th>
            <th class="px-3 py-2 text-right w-32">Precio USD</th>
            <th class="px-3 py-2 text-right w-24">Desc. %</th>
            <th class="w-10"></th>
        </tr>
    </thead>
    <tbody id="lineas-body" data-next="{{ $nextIdx }}">
        @foreach ($lineas as $i => $l)
        <tr>
            <td class="px-3 py-2">
                <select name="lineas[{{ $i }}][id_variante]" class="w-full border rounded px-2 py-1" required>
                    <option value="">— Variante —</option>
                    @foreach ($variantes as $v)
                    <option value="{{ $v->id_variante }}"
                        @selected((string) ($l['id_variante'] ?? '' )===(string) $v->id_variante)>
                        {{ $v->sku }} — {{ $v->producto->nombre }}{{ $v->color ? ' ('.$v->color.')' : '' }}
                    </option>
                    @endforeach
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="number" name="lineas[{{ $i }}][cantidad]" min="1" required
                    value="{{ $l['cantidad'] ?? 1 }}"
                    class="w-full border rounded px-2 py-1 text-right">
            </td>
            <td class="px-3 py-2">
                <input type="number" name="lineas[{{ $i }}][precio_unitario_usd]"
                    step="0.01" min="0" required
                    value="{{ $l['precio_unitario_usd'] ?? '' }}"
                    class="w-full border rounded px-2 py-1 text-right">
            </td>
            <td class="px-3 py-2">
                <input type="number" name="lineas[{{ $i }}][descuento_pct]"
                    step="0.01" min="0" max="100"
                    value="{{ $l['descuento_pct'] ?? 0 }}"
                    class="w-full border rounded px-2 py-1 text-right">
            </td>
            <td class="px-3 py-2">
                <button type="button" class="remove text-red-600" title="Quitar">✕</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-2">
    <button type="button" id="add-linea"
        class="text-blue-600 hover:underline text-sm">+ Agregar línea</button>
</div>

<template id="linea-tpl">
    <tr>
        <td class="px-3 py-2">
            <select data-name="id_variante" class="w-full border rounded px-2 py-1" required>
                <option value="">— Variante —</option>
                @foreach ($variantes as $v)
                <option value="{{ $v->id_variante }}">
                    {{ $v->sku }} — {{ $v->producto->nombre }}{{ $v->color ? ' ('.$v->color.')' : '' }}
                </option>
                @endforeach
            </select>
        </td>
        <td class="px-3 py-2">
            <input type="number" data-name="cantidad" min="1" value="1" required
                class="w-full border rounded px-2 py-1 text-right">
        </td>
        <td class="px-3 py-2">
            <input type="number" data-name="precio_unitario_usd" step="0.01" min="0" required
                class="w-full border rounded px-2 py-1 text-right">
        </td>
        <td class="px-3 py-2">
            <input type="number" data-name="descuento_pct" step="0.01" min="0" max="100" value="0"
                class="w-full border rounded px-2 py-1 text-right">
        </td>
        <td class="px-3 py-2">
            <button type="button" class="remove text-red-600" title="Quitar">✕</button>
        </td>
    </tr>
</template>

<div class="mt-6 flex gap-2">
    <button class="bg-blue-600 ...">Emitir cotización</button>
    <a href="..." class="border ...">Cancelar</a>
</div>

{{-- Reemplaza el @push('scripts') ... @endpush por esto: --}}
<script>
    (function() {
        const tbody = document.getElementById('lineas-body');
        const tpl = document.getElementById('linea-tpl');
        if (!tbody || !tpl) return;

        let idx = Number(tbody.dataset.next || 0);

        function bindRemove(tr) {
            const btn = tr.querySelector('.remove');
            if (btn) btn.addEventListener('click', () => tr.remove());
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
        }

        document.getElementById('add-linea')?.addEventListener('click', addRow);
        tbody.querySelectorAll('tr').forEach(bindRemove);
        if (tbody.children.length === 0) addRow();
    })();
</script>