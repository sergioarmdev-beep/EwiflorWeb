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

    <!-- Campo Contraseña con Ojo Interactivo -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">
            Contraseña {{ $esEdicion ? '(dejar vacío para no cambiar)' : '*' }}
        </label>
        <div class="relative mt-1">
            <input type="password" name="password" id="password"
                placeholder="••••••••"
                class="w-full px-3.5 py-2.5 pr-12 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all"
                {{ $esEdicion ? '' : 'required' }} minlength="8">

            <button type="button" onclick="toggleFieldVisibility('password', 'eye-open-1', 'eye-closed-1')"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none transition-colors cursor-pointer"
                title="Ver contraseña">
                <svg id="eye-open-1" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg id="eye-closed-1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
            </button>
        </div>
        @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Campo Confirmar Contraseña con Ojo Interactivo -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Confirmar contraseña</label>
        <div class="relative mt-1">
            <input type="password" name="password_confirmation" id="password_confirmation"
                placeholder="••••••••"
                class="w-full px-3.5 py-2.5 pr-12 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all"
                {{ $esEdicion ? '' : 'required' }} minlength="8">

            <button type="button" onclick="toggleFieldVisibility('password_confirmation', 'eye-open-2', 'eye-closed-2')"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none transition-colors cursor-pointer"
                title="Ver contraseña">
                <svg id="eye-open-2" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg id="eye-closed-2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Script para alternar la visibilidad de los campos de contraseña -->
<script>
    function toggleFieldVisibility(inputId, openIconId, closedIconId) {
        const input = document.getElementById(inputId);
        const eyeOpen = document.getElementById(openIconId);
        const eyeClosed = document.getElementById(closedIconId);

        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        } else {
            input.type = 'password';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        }
    }
</script>