{{-- Una fila de variante. Variables: $i (índice), $fila (array con los valores) --}}
@php
    $puedeEditarPrecio = in_array(auth()->user()?->nombre_rol, ['GERENTE', 'ADMINISTRADOR'], true);
@endphp

<div data-variante-row class="rounded-xl border border-line bg-surface-2/60 p-4 sm:p-5">

    <input type="hidden" name="variantes[{{ $i }}][id_variante]" value="{{ $fila['id_variante'] ?? '' }}">

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

        <div class="md:col-span-3">
            <label class="block text-xs font-medium text-muted mb-1.5">SKU *</label>
            <input type="text" name="variantes[{{ $i }}][sku]" maxlength="40" required
                value="{{ $fila['sku'] ?? '' }}" placeholder="Ej. EQ-001-ROJO"
                class="form-input font-mono">
            @if($i !== '__I__')
            @error("variantes.$i.sku")
            <span class="form-error">{{ $message }}</span>
            @enderror
            @endif
        </div>

        <div class="md:col-span-3">
            <label class="block text-xs font-medium text-muted mb-1.5">Precio USD</label>

            @if($puedeEditarPrecio)
            <input type="number" name="variantes[{{ $i }}][precio_usd]" step="0.01" min="0.01"
                value="{{ $fila['precio_usd'] ?? '' }}" placeholder="Ej. 129.99"
                class="form-input text-right">
            @if($i !== '__I__')
            @error("variantes.$i.precio_usd")
            <span class="form-error">{{ $message }}</span>
            @enderror
            @endif
            @else
            <p class="form-input text-right text-muted">
                {{ isset($fila['precio_usd']) && $fila['precio_usd'] !== null ? number_format((float) $fila['precio_usd'], 2) : '—' }}
            </p>
            @endif
        </div>

        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-muted mb-1.5">Color</label>
            <input type="text" name="variantes[{{ $i }}][color]" maxlength="30"
                value="{{ $fila['color'] ?? '' }}" placeholder="Ej. Acero"
                class="form-input">
        </div>

        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-muted mb-1.5">Estado</label>
            @php $estadoFila = $fila['estado'] ?? 'ACTIVO'; @endphp
            <select name="variantes[{{ $i }}][estado]" class="form-select">
                <option value="ACTIVO" {{ $estadoFila === 'ACTIVO' ? 'selected' : '' }}>Activa</option>
                <option value="INACTIVO" {{ $estadoFila === 'INACTIVO' ? 'selected' : '' }}>Inactiva</option>
            </select>
        </div>

        <div class="md:col-span-2 flex md:items-end md:justify-end">
            <button type="button" data-variante-remove
                class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl border border-red-500/30 bg-red-500/10 text-red-500 hover:bg-red-500/20 text-xs font-semibold transition duration-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v12a1 1 0 01-1 1H8a1 1 0 01-1-1V7h10z" />
                </svg>
                Quitar
            </button>
        </div>

        <div class="md:col-span-12">
            <label class="block text-xs font-medium text-muted mb-1.5">Características</label>
            <input type="text" name="variantes[{{ $i }}][caracteristicas]" maxlength="200"
                value="{{ $fila['caracteristicas'] ?? '' }}"
                placeholder="Ej. 220V, 3 bandejas, capacidad 40 L"
                class="form-input">
        </div>

    </div>
</div>
