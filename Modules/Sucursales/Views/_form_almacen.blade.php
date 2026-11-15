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
                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Nombre <span class="text-red-500">*</span>
            </label>

            <input type="text" id="nombre" name="nombre"
                value="{{ old('nombre', $almacen->nombre) }}"
                placeholder="Ej. Almacén La Paz Central"
                required
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                       bg-white dark:bg-slate-950
                       text-slate-800 dark:text-slate-100
                       placeholder:text-slate-400 dark:placeholder:text-slate-500
                       border-slate-300 dark:border-slate-700
                       focus:outline-none focus:ring-2 focus:ring-sky-500/30
                       focus:border-sky-500 transition-colors
                       @error('nombre') border-red-500 dark:border-red-500 @enderror">

            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                El nombre debe ser único en todo el sistema.
            </p>

            @error('nombre')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tamaño --}}
        <div>
            <label for="tamano"
                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Tamaño <span class="text-red-500">*</span>
            </label>

            <select id="tamano" name="tamano"
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                       bg-white dark:bg-slate-950
                       text-slate-800 dark:text-slate-100
                       border-slate-300 dark:border-slate-700
                       focus:outline-none focus:ring-2 focus:ring-sky-500/30
                       focus:border-sky-500 transition-colors
                       @error('tamano') border-red-500 dark:border-red-500 @enderror">
                <option value="">Seleccionar tamaño</option>

                @foreach ($tamanos as $t)
                <option value="{{ $t }}"
                    @selected(old('tamano', $almacen->tamano) === $t)>
                    {{ $t === 'GRANDE' ? 'Grande' : 'Pequeño' }}
                </option>
                @endforeach
            </select>

            @error('tamano')
            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Sucursal --}}
        <div class="md:col-span-2">
            <label for="id_sucursal"
                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Sucursal <span class="text-red-500">*</span>
            </label>

            <select id="id_sucursal" name="id_sucursal"
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                       bg-white dark:bg-slate-950
                       text-slate-800 dark:text-slate-100
                       border-slate-300 dark:border-slate-700
                       focus:outline-none focus:ring-2 focus:ring-sky-500/30
                       focus:border-sky-500 transition-colors
                       @error('id_sucursal') border-red-500 dark:border-red-500 @enderror">

                @forelse ($sucursales as $s)
                <option value="{{ $s->id_sucursal }}"
                    @selected((int) old('id_sucursal', $sucursalActual) === $s->id_sucursal)>
                    {{ $s->nombre }}
                </option>
                @empty
                <option value="">No hay sucursales activas</option>
                @endforelse
            </select>

            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                Mover el almacén a otra sucursal no altera sus existencias registradas.
            </p>

            @error('id_sucursal')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Acciones --}}
    <div class="pt-5 border-t border-slate-200 dark:border-slate-800
                flex flex-col-reverse sm:flex-row sm:justify-end gap-3">

        <a href="{{ $isEdit && $almacen->sucursal
                    ? route('sucursales.show', $almacen->sucursal)
                    : route('sucursales.index') }}"
            class="inline-flex items-center justify-center gap-2
                  px-5 py-2.5 rounded-lg text-sm font-medium
                  border border-slate-300 dark:border-slate-700
                  text-slate-700 dark:text-slate-300
                  hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            Cancelar
        </a>

        <button type="submit"
            class="inline-flex items-center justify-center gap-2
                   px-5 py-2.5 rounded-lg text-sm font-semibold
                   bg-sky-600 hover:bg-sky-700
                   text-white shadow-sm
                   focus:outline-none focus:ring-2
                   focus:ring-sky-500 focus:ring-offset-2
                   dark:focus:ring-offset-slate-900 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>

            {{ $isEdit ? 'Guardar cambios' : 'Crear almacén' }}
        </button>
    </div>
</div>
