@php($esEdicion = $modo === 'edit')

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-100">
    <!-- Campo Nombre Completo -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Nombre completo *</label>
        <input type="text" name="nombre_completo"
            value="{{ old('nombre_completo', $usuario->nombre_completo) }}"
            placeholder="Ej. Carlos Mendoza"
            class="mt-1 w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all" required maxlength="100">
        @error('nombre_completo') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Campo Nombre de Usuario -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Nombre de usuario *</label>
        <input type="text" name="nombre_usuario"
            value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}"
            placeholder="Ej. cmendoza"
            class="mt-1 w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 placeholder-slate-500 text-sm font-mono focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all" required maxlength="30">
        @error('nombre_usuario') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Campo Correo -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Correo</label>
        <input type="email" name="correo"
            value="{{ old('correo', $usuario->correo) }}"
            placeholder="correo@empresa.com"
            class="mt-1 w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all" maxlength="100">
        @error('correo') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Campo Rol -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Rol *</label>
        <select name="nombre_rol" class="mt-1 w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all cursor-pointer" required>
            @foreach ($roles as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('nombre_rol', $usuario->nombre_rol) === $valor) class="bg-slate-900 text-slate-100">
                {{ $etiqueta }}
            </option>
            @endforeach
        </select>
        @error('nombre_rol') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Campo Sucursal -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Sucursal</label>
        <select name="id_sucursal" class="mt-1 w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all cursor-pointer">
            <option value="" class="bg-slate-900 text-slate-400">— Sin sucursal —</option>
            @foreach ($sucursales as $s)
            <option value="{{ $s->id_sucursal }}"
                @selected((int) old('id_sucursal', $usuario->id_sucursal) === (int) $s->id_sucursal) class="bg-slate-900 text-slate-100">
                {{ $s->nombre }}
            </option>
            @endforeach
        </select>
        @error('id_sucursal') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Campo Comisión -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">% Comisión</label>
        <input type="number" step="0.01" min="0" max="100" name="porcentaje_comision_pct"
            value="{{ old('porcentaje_comision_pct', $usuario->porcentaje_comision_pct ?? 0) }}"
            class="mt-1 w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all" required>
        @error('porcentaje_comision_pct') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Campo Estado -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Estado *</label>
        <select name="estado" class="mt-1 w-full px-3.5 py-2.5 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all cursor-pointer" required>
            <option value="ACTIVO" @selected(old('estado', $usuario->estado) === 'ACTIVO') class="bg-slate-900 text-slate-100">Activo</option>
            <option value="INACTIVO" @selected(old('estado', $usuario->estado) === 'INACTIVO') class="bg-slate-900 text-slate-100">Inactivo</option>
        </select>
        @error('estado') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Contraseña: campos + medidor de fortaleza + generador (parcial reutilizable) -->
    @include('auth::partials.password-fields', [
        'requerido' => !$esEdicion,
        'etiqueta'  => 'Contraseña',
    ])
</div>
