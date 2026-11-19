@php
$isEdit = $almacen->exists;
$tamanos = \Modules\Sucursales\Support\Catalogos::TAMANOS_ALMACEN;
$sucursalActual = $almacen->id_sucursal ?? ($sucursal->id_sucursal ?? old('id_sucursal'));
@endphp

<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Nombre --}}
        <div>
            <label for="nombre"
                class="block text-sm font-medium text-muted mb-1.5">
                Nombre <span class="text-red-500">*</span>
            </label>

            <input type="text" id="nombre" name="nombre"
                value="{{ old('nombre', $almacen->nombre) }}"
                placeholder="Ej. Almacén La Paz Central"
                required
                class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-brand-green/20 @error('nombre') border-red-500 @enderror">

            <p class="mt-1.5 text-xs text-muted">
                El nombre debe ser único en todo el sistema.
            </p>

            @error('nombre')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tamaño --}}
        <div>
            <label for="tamano"
                class="block text-sm font-medium text-muted mb-1.5">
                Tamaño <span class="text-red-500">*</span>
            </label>

            <select id="tamano" name="tamano"
                class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm cursor-pointer focus:border-brand-green/50 focus:ring-brand-green/20 @error('tamano') border-red-500 @enderror">
                <option value="">Seleccionar tamaño</option>

                @foreach ($tamanos as $t)
                <option value="{{ $t }}"
                    @selected(old('tamano', $almacen->tamano) === $t)>
                    {{ $t === 'GRANDE' ? 'Grande' : 'Pequeño' }}
                </option>
                @endforeach
            </select>

            @error('tamano')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Sucursal --}}
        <div class="md:col-span-2">
            <label for="id_sucursal"
                class="block text-sm font-medium text-muted mb-1.5">
                Sucursal <span class="text-red-500">*</span>
            </label>

            <select id="id_sucursal" name="id_sucursal"
                class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm cursor-pointer focus:border-brand-green/50 focus:ring-brand-green/20 @error('id_sucursal') border-red-500 @enderror">

                @forelse ($sucursales as $s)
                <option value="{{ $s->id_sucursal }}"
                    @selected((int) old('id_sucursal', $sucursalActual) === $s->id_sucursal)>
                    {{ $s->nombre }}
                </option>
                @empty
                <option value="">No hay sucursales activas</option>
                @endforelse
            </select>

            <p class="mt-1.5 text-xs text-muted">
                Mover el almacén a otra sucursal no altera sus existencias registradas.
            </p>

            @error('id_sucursal')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Acciones --}}
    <div class="pt-5 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-3">

        <a href="{{ $isEdit && $almacen->sucursal
                    ? route('sucursales.show', $almacen->sucursal)
                    : route('sucursales.index') }}"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border border-line text-ink hover:bg-surface-2 hover:text-brand-green transition-colors">
            Cancelar
        </a>

        <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-brand-green text-white shadow-sm hover:bg-brand-green-dark focus:ring-brand-green/40 focus:ring-offset-surface transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>

            {{ $isEdit ? 'Guardar cambios' : 'Crear almacén' }}
        </button>
    </div>
</div>
