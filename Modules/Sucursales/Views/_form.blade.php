@php
$isEdit = $sucursal->exists;
$departamentos = \Modules\Sucursales\Support\Catalogos::DEPARTAMENTOS;
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
                value="{{ old('nombre', $sucursal->nombre) }}"
                placeholder="Ej. Cochabamba"
                required
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                       bg-white dark:bg-slate-950
                       text-slate-800 dark:text-slate-100
                       placeholder:text-slate-400 dark:placeholder:text-slate-500
                       border-slate-300 dark:border-slate-700
                       focus:outline-none focus:ring-2 focus:ring-sky-500/30
                       focus:border-sky-500 transition-colors
                       @error('nombre') border-red-500 dark:border-red-500 @enderror">

            @error('nombre')
            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Departamento --}}
        <div>
            <label for="departamento"
                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Departamento <span class="text-red-500">*</span>
            </label>

            <select id="departamento" name="departamento"
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                       bg-white dark:bg-slate-950
                       text-slate-800 dark:text-slate-100
                       border-slate-300 dark:border-slate-700
                       focus:outline-none focus:ring-2 focus:ring-sky-500/30
                       focus:border-sky-500 transition-colors
                       @error('departamento') border-red-500 dark:border-red-500 @enderror">
                <option value="">Seleccionar departamento</option>

                @foreach ($departamentos as $d)
                <option value="{{ $d }}"
                    @selected(old('departamento', $sucursal->departamento) === $d)>
                    {{ \Illuminate\Support\Str::of($d)->replace('_', ' ')->title() }}
                </option>
                @endforeach
            </select>

            @error('departamento')
            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Dirección --}}
        <div>
            <label for="direccion"
                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Dirección
            </label>

            <input type="text" id="direccion" name="direccion"
                value="{{ old('direccion', $sucursal->direccion) }}"
                placeholder="Dirección física de la sucursal"
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                       bg-white dark:bg-slate-950
                       text-slate-800 dark:text-slate-100
                       placeholder:text-slate-400 dark:placeholder:text-slate-500
                       border-slate-300 dark:border-slate-700
                       focus:outline-none focus:ring-2 focus:ring-sky-500/30
                       focus:border-sky-500 transition-colors
                       @error('direccion') border-red-500 dark:border-red-500 @enderror">

            @error('direccion')
            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Teléfono --}}
        <div>
            <label for="telefono"
                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Teléfono
            </label>

            <input type="text" id="telefono" name="telefono"
                value="{{ old('telefono', $sucursal->telefono) }}"
                placeholder="Ej. 2-2441234"
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm
                       bg-white dark:bg-slate-950
                       text-slate-800 dark:text-slate-100
                       placeholder:text-slate-400 dark:placeholder:text-slate-500
                       border-slate-300 dark:border-slate-700
                       focus:outline-none focus:ring-2 focus:ring-sky-500/30
                       focus:border-sky-500 transition-colors
                       @error('telefono') border-red-500 dark:border-red-500 @enderror">

            @error('telefono')
            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Ubicación geográfica (Leaflet + OpenStreetMap) --}}
    <div class="pt-5 border-t border-slate-200 dark:border-slate-800">
        @include('sucursales::partials.mapa-picker', ['sucursal' => $sucursal])
    </div>

    {{-- Acciones --}}
    <div class="pt-5 border-t border-slate-200 dark:border-slate-800
                flex flex-col-reverse sm:flex-row sm:justify-end gap-3">

        <a href="{{ route('sucursales.index') }}"
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

            {{ $isEdit ? 'Guardar cambios' : 'Crear sucursal' }}
        </button>
    </div>
</div>
