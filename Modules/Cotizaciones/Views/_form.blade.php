@php
$isEdit = $cotizacion->exists;
$lineas = old('lineas', $lineas ?? []);
$nextIdx = count($lineas);
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-muted">Cliente *</label>
        <select name="id_cliente" id="id_cliente" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-brand-green/20" required>
            <option value="">— Selecciona un cliente —</option>
            @foreach ($clientes as $cli)
            <option value="{{ $cli->id_cliente }}"
                @selected((string) old('id_cliente', $cotizacion->id_cliente) === (string) $cli->id_cliente)>
                {{ $cli->nombre }} — {{ $cli->telefono }}
            </option>
            @endforeach
        </select>
        @error('id_cliente') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

        @include('clientes::partials.quick-create')
    </div>
    <div>
        <label class="block text-sm font-medium text-muted">Vigencia (días) *</label>
        <input type="number" name="vigencia_dias" min="1" max="90" required
            value="{{ old('vigencia_dias', $cotizacion->vigencia_dias ?? 14) }}"
            class="w-full border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-brand-green/20">
        @error('vigencia_dias') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-3">
        <label class="block text-sm font-medium text-muted">Observación</label>
        <textarea name="observacion" rows="2" maxlength="200"
            class="w-full border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-brand-green/20">{{ old('observacion', $cotizacion->observacion) }}</textarea>
    </div>
</div>

<h2 class="font-bold text-ink mb-2">Líneas de la cotización</h2>

@if ($errors->has('lineas'))
<div class="mb-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-500">{{ $errors->first('lineas') }}</div>
@endif

<div class="overflow-x-auto rounded-xl border border-line bg-surface shadow-sm">
<table class="w-full text-sm">
    <thead class="bg-surface-2">
        <tr>
            <th scope="col" class="px-3 py-2 text-left min-w-[240px] text-xs font-semibold uppercase tracking-wider text-muted">Variante</th>
            <th scope="col" class="px-3 py-2 text-right w-20 text-xs font-semibold uppercase tracking-wider text-muted">Cantidad</th>
            <th scope="col" class="px-3 py-2 text-right w-28 text-xs font-semibold uppercase tracking-wider text-muted">Precio USD</th>
            <th scope="col" class="px-3 py-2 text-right w-20 text-xs font-semibold uppercase tracking-wider text-muted">Garantía (meses)</th>
            <th scope="col" class="px-3 py-2 text-right w-24 text-xs font-semibold uppercase tracking-wider text-muted">Tipo desc.</th>
            <th scope="col" class="px-3 py-2 text-right w-28 text-xs font-semibold uppercase tracking-wider text-muted">Descuento</th>
            <th scope="col" class="px-3 py-2 text-right w-28 text-xs font-semibold uppercase tracking-wider text-muted">Subtotal USD</th>
            <th scope="col" class="w-10"></th>
        </tr>
    </thead>
    <tbody id="lineas-body" data-next="{{ $nextIdx }}" class="divide-y divide-line">
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
                    class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20">
            </td>
            <td class="px-3 py-2">
                <input type="number" readonly tabindex="-1" placeholder="—"
                    name="lineas[{{ $i }}][precio_unitario_usd]"
                    step="0.01" value="{{ $l['precio_unitario_usd'] ?? '' }}"
                    class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right cursor-not-allowed">
            </td>
            <td class="px-3 py-2"><input type="number" name="lineas[{{ $i }}][garantia_meses]" min="0" value="{{ $l['garantia_meses'] ?? $garantias[$l['id_variante']] ?? 0 }}" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20" placeholder="0"></td>
            <td class="px-3 py-2">
                <select name="lineas[{{ $i }}][descuento_tipo]" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 focus:border-brand-green/50 focus:ring-brand-green/20">
                    <option value="PCT" @selected(($l['descuento_tipo'] ?? 'PCT') === 'PCT')>%</option>
                    <option value="USD" @selected(($l['descuento_tipo'] ?? '') === 'USD')>USD</option>
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="number" name="lineas[{{ $i }}][descuento_valor]"
                    step="0.01" min="0"
                    value="{{ $l['descuento_valor'] ?? ($l['descuento_pct'] ?? 0) }}"
                    class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20">
            </td>
            <td class="px-3 py-2 text-right text-muted"><span data-subtotal>0.00</span></td>
            <td class="px-3 py-2">
                <button type="button" class="remove text-red-500" title="Quitar">✕</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>

<p class="text-xs text-muted mt-2">
    El precio se toma del catálogo y no puede editarse. La garantía por defecto viene del catálogo
    y puede editarse por línea. Solo se permite un descuento por línea,
    en porcentaje o en monto USD sobre el total de la línea.
    Si la variante tiene promoción vigente, el descuento se precarga al elegirla;
    el Vendedor no puede aplicar un descuento mayor al de la promoción (solo Gerente o Administrador).
</p>

<div class="mt-2">
    <button type="button" id="add-linea"
        class="text-brand-blue hover:underline text-sm">+ Agregar línea</button>
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
                class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20">
        </td>
        <td class="px-3 py-2">
            <input type="number" data-name="precio_unitario_usd" readonly tabindex="-1" placeholder="—"
                step="0.01"
                class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right cursor-not-allowed">
        </td>
        <td class="px-3 py-2"><input type="number" data-name="garantia_meses" min="0" value="0" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20" placeholder="0"></td>
        <td class="px-3 py-2">
            <select data-name="descuento_tipo" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="PCT">%</option>
                <option value="USD">USD</option>
            </select>
        </td>
        <td class="px-3 py-2">
            <input type="number" data-name="descuento_valor" step="0.01" min="0" value="0"
                class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20">
        </td>
        <td class="px-3 py-2 text-right text-muted"><span data-subtotal>0.00</span></td>
        <td class="px-3 py-2">
            <button type="button" class="remove text-red-500" title="Quitar">✕</button>
        </td>
    </tr>
</template>

<div class="mt-6 flex gap-2">
    <button type="submit" class="bg-brand-green text-white px-4 py-2 rounded-xl font-semibold hover:bg-brand-green-dark transition-colors">
        {{ $isEdit ? 'Actualizar cotización' : 'Emitir cotización' }}
    </button>
    <a href="{{ route('cotizaciones.index') }}" class="border border-line rounded-xl px-4 py-2 text-ink hover:bg-surface-2 transition-colors">Cancelar</a>
</div>

@include('components.lineas-documento-scripts', [
    'precios' => $precios ?? [],
    'garantias' => $garantias ?? [],
    'promociones' => $promociones ?? [],
    'puedeEditarDescuento' => $puedeEditarDescuento ?? true,
])
