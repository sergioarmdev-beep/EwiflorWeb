@php($esEdicion = $modo === 'edit')

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Nombre completo --}}
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Nombre completo *</label>
        <input type="text" name="nombre_completo"
            value="{{ old('nombre_completo', $usuario->nombre_completo) }}"
            placeholder="Ej. Carlos Mendoza"
            class="mt-1 w-full px-3.5 py-2.5 rounded-xl text-sm transition-all focus:outline-none focus:ring-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-[#0B6E45] dark:focus:border-emerald-500 focus:ring-[#0B6E45]/30 dark:focus:ring-emerald-500/30"
            required maxlength="100">
        @error('nombre_completo') <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Nombre de usuario --}}
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Nombre de usuario *</label>
        <input type="text" name="nombre_usuario"
            value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}"
            placeholder="Ej. cmendoza"
            class="mt-1 w-full px-3.5 py-2.5 rounded-xl text-sm font-mono transition-all focus:outline-none focus:ring-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-[#0B6E45] dark:focus:border-emerald-500 focus:ring-[#0B6E45]/30 dark:focus:ring-emerald-500/30"
            required maxlength="30">
        @error('nombre_usuario') <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Correo --}}
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Correo</label>
        <input type="email" name="correo"
            value="{{ old('correo', $usuario->correo) }}"
            placeholder="correo@empresa.com"
            class="mt-1 w-full px-3.5 py-2.5 rounded-xl text-sm transition-all focus:outline-none focus:ring-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-[#0B6E45] dark:focus:border-emerald-500 focus:ring-[#0B6E45]/30 dark:focus:ring-emerald-500/30"
            maxlength="100">
        @error('correo') <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Rol --}}
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Rol *</label>
        <select name="nombre_rol"
            class="mt-1 w-full px-3.5 py-2.5 rounded-xl text-sm cursor-pointer transition-all focus:outline-none focus:ring-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:border-[#0B6E45] dark:focus:border-emerald-500 focus:ring-[#0B6E45]/30 dark:focus:ring-emerald-500/30"
            required>
            @foreach ($roles as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('nombre_rol', $usuario->nombre_rol) === $valor)
                class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                {{ $etiqueta }}
            </option>
            @endforeach
        </select>
        @error('nombre_rol') <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Sucursal --}}
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Sucursal</label>
        <select name="id_sucursal"
            class="mt-1 w-full px-3.5 py-2.5 rounded-xl text-sm cursor-pointer transition-all focus:outline-none focus:ring-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:border-[#0B6E45] dark:focus:border-emerald-500 focus:ring-[#0B6E45]/30 dark:focus:ring-emerald-500/30">
            <option value="" class="bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400">— Sin sucursal —</option>
            @foreach ($sucursales as $s)
            <option value="{{ $s->id_sucursal }}"
                @selected((int) old('id_sucursal', $usuario->id_sucursal) === (int) $s->id_sucursal)
                class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                {{ $s->nombre }}
            </option>
            @endforeach
        </select>
        @error('id_sucursal') <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Comisión --}}
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">% Comisión</label>
        <input type="number" step="0.01" min="0" max="100" name="porcentaje_comision_pct"
            value="{{ old('porcentaje_comision_pct', $usuario->porcentaje_comision_pct ?? 0) }}"
            class="mt-1 w-full px-3.5 py-2.5 rounded-xl text-sm transition-all focus:outline-none focus:ring-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-[#0B6E45] dark:focus:border-emerald-500 focus:ring-[#0B6E45]/30 dark:focus:ring-emerald-500/30"
            required>
        @error('porcentaje_comision_pct') <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Estado --}}
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Estado *</label>
        <select name="estado"
            class="mt-1 w-full px-3.5 py-2.5 rounded-xl text-sm cursor-pointer transition-all focus:outline-none focus:ring-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:border-[#0B6E45] dark:focus:border-emerald-500 focus:ring-[#0B6E45]/30 dark:focus:ring-emerald-500/30"
            required>
            <option value="ACTIVO" @selected(old('estado', $usuario->estado) === 'ACTIVO')
                class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Activo</option>
            <option value="INACTIVO" @selected(old('estado', $usuario->estado) === 'INACTIVO')
                class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">Inactivo</option>
        </select>
        @error('estado') <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Contraseña: campos + medidor de fortaleza + generador (parcial reutilizable) --}}
    @include('auth::partials.password-fields', [
    'requerido' => !$esEdicion,
    'etiqueta' => 'Contraseña',
    ])
</div>