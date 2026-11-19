@php
$isEdit = $sucursal->exists;
$departamentos = \Modules\Sucursales\Support\Catalogos::DEPARTAMENTOS;
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
                value="{{ old('nombre', $sucursal->nombre) }}"
                placeholder="Ej. Cochabamba"
                required
                class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-brand-green/20 @error('nombre') border-red-500 @enderror">

            @error('nombre')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Departamento --}}
        <div>
            <label for="departamento"
                class="block text-sm font-medium text-muted mb-1.5">
                Departamento <span class="text-red-500">*</span>
            </label>

            <select id="departamento" name="departamento"
                class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm cursor-pointer focus:border-brand-green/50 focus:ring-brand-green/20 @error('departamento') border-red-500 @enderror">
                <option value="">Seleccionar departamento</option>

                @foreach ($departamentos as $d)
                <option value="{{ $d }}"
                    @selected(old('departamento', $sucursal->departamento) === $d)>
                    {{ \Illuminate\Support\Str::of($d)->replace('_', ' ')->title() }}
                </option>
                @endforeach
            </select>

            @error('departamento')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Dirección --}}
        <div>
            <label for="direccion"
                class="block text-sm font-medium text-muted mb-1.5">
                Dirección
            </label>

            <input type="text" id="direccion" name="direccion"
                value="{{ old('direccion', $sucursal->direccion) }}"
                placeholder="Dirección física de la sucursal"
                class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-brand-green/20 @error('direccion') border-red-500 @enderror">

            @error('direccion')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Teléfono --}}
        <div>
            <label for="telefono"
                class="block text-sm font-medium text-muted mb-1.5">
                Teléfono
            </label>

            <input type="text" id="telefono" name="telefono"
                value="{{ old('telefono', $sucursal->telefono) }}"
                placeholder="Ej. 2-2441234"
                class="w-full rounded-xl border border-line bg-surface-2 text-ink px-3.5 py-2.5 text-sm placeholder:text-muted focus:border-brand-green/50 focus:ring-brand-green/20 @error('telefono') border-red-500 @enderror">

            @error('telefono')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Ubicación geográfica (Leaflet + OpenStreetMap) --}}
    <div class="pt-5 border-t border-line">
        @include('sucursales::partials.mapa-picker', ['sucursal' => $sucursal])
    </div>

    {{-- Acciones --}}
    <div class="pt-5 border-t border-line flex flex-col-reverse sm:flex-row sm:justify-end gap-3">

        <a href="{{ route('sucursales.index') }}"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border border-line text-ink hover:bg-surface-2 hover:text-brand-green transition-colors">
            Cancelar
        </a>

        <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-brand-green text-white shadow-sm hover:bg-brand-green-dark focus:ring-brand-green/40 focus:ring-offset-surface transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>

            {{ $isEdit ? 'Guardar cambios' : 'Crear sucursal' }}
        </button>
    </div>
</div>
