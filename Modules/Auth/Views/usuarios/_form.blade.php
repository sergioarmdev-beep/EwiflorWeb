@php($esEdicion = $modo === 'edit')

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Nombre completo *</label>
        <input type="text" name="nombre_completo"
            value="{{ old('nombre_completo', $usuario->nombre_completo) }}"
            class="mt-1 border-gray-300 rounded-md shadow-sm w-full" required maxlength="100">
        @error('nombre_completo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Nombre de usuario *</label>
        <input type="text" name="nombre_usuario"
            value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}"
            class="mt-1 border-gray-300 rounded-md shadow-sm w-full font-mono" required maxlength="30">
        @error('nombre_usuario') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Correo</label>
        <input type="email" name="correo"
            value="{{ old('correo', $usuario->correo) }}"
            class="mt-1 border-gray-300 rounded-md shadow-sm w-full" maxlength="100">
        @error('correo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Rol *</label>
        <select name="nombre_rol" class="mt-1 border-gray-300 rounded-md shadow-sm w-full" required>
            @foreach ($roles as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('nombre_rol', $usuario->nombre_rol) === $valor)>
                {{ $etiqueta }}
            </option>
            @endforeach
        </select>
        @error('nombre_rol') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Sucursal</label>
        <select name="id_sucursal" class="mt-1 border-gray-300 rounded-md shadow-sm w-full">
            <option value="">— Sin sucursal —</option>
            @foreach ($sucursales as $s)
            <option value="{{ $s->id_sucursal }}"
                @selected((int) old('id_sucursal', $usuario->id_sucursal) === (int) $s->id_sucursal)>
                {{ $s->nombre }}
            </option>
            @endforeach
        </select>
        @error('id_sucursal') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">% Comisión</label>
        <input type="number" step="0.01" min="0" max="100" name="porcentaje_comision_pct"
            value="{{ old('porcentaje_comision_pct', $usuario->porcentaje_comision_pct ?? 0) }}"
            class="mt-1 border-gray-300 rounded-md shadow-sm w-full" required>
        @error('porcentaje_comision_pct') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Estado *</label>
        <select name="estado" class="mt-1 border-gray-300 rounded-md shadow-sm w-full" required>
            <option value="ACTIVO" @selected(old('estado', $usuario->estado) === 'ACTIVO')>Activo</option>
            <option value="INACTIVO" @selected(old('estado', $usuario->estado) === 'INACTIVO')>Inactivo</option>
        </select>
        @error('estado') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">
            Contraseña {{ $esEdicion ? '(dejar vacío para no cambiar)' : '*' }}
        </label>
        <input type="password" name="password" class="mt-1 border-gray-300 rounded-md shadow-sm w-full"
            {{ $esEdicion ? '' : 'required' }} minlength="8">
        @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
        <input type="password" name="password_confirmation"
            class="mt-1 border-gray-300 rounded-md shadow-sm w-full"
            {{ $esEdicion ? '' : 'required' }} minlength="8">
    </div>
</div>