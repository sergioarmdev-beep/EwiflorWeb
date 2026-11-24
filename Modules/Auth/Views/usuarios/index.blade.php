@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-ink">

    {{-- Encabezado de la Sección --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-ink">Gestión de Usuarios</h1>
            <p class="text-xs text-muted mt-0.5">Administra los accesos, roles y sucursales del personal</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('usuarios.solicitudes-contrasena.index') }}"
                class="relative inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-surface-2 border border-line text-ink text-sm font-semibold rounded-xl hover:bg-surface-2 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                <span>Claves olvidadas</span>
                @if ($resumen['solicitudes_contrasena'] > 0)
                <span class="min-w-[1.25rem] px-1.5 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-bold leading-none text-center">{{ $resumen['solicitudes_contrasena'] }}</span>
                @endif
            </a>
            <a href="{{ route('usuarios.solicitudes.index') }}"
                class="relative inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-surface-2 border border-line text-ink text-sm font-semibold rounded-xl hover:bg-surface-2 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Solicitudes</span>
                @if ($resumen['solicitudes'] > 0)
                <span class="min-w-[1.25rem] px-1.5 py-0.5 rounded-full bg-brand-green text-white text-[10px] font-bold leading-none text-center">{{ $resumen['solicitudes'] }}</span>
                @endif
            </a>
            <a href="{{ route('usuarios.create') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-green text-white text-sm font-bold rounded-xl shadow-sm hover:bg-brand-green-dark transition-all duration-150 active:scale-[0.99] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nuevo Usuario</span>
            </a>
        </div>
    </div>


    {{-- Contraseña temporal (se muestra una sola vez) --}}
    @include('auth::partials.password-temporal')

    {{-- Tarjetas de resumen (totales de todos los usuarios, sin filtros) --}}
    @php
        $nombresRol = ['GERENTE' => 'Gerentes', 'ADMINISTRADOR' => 'Administradores', 'VENDEDOR' => 'Vendedores', 'SERVICIO_TECNICO' => 'Serv. técnico'];
        $tarjeta = 'p-4 rounded-2xl border border-line bg-surface shadow-soft hover:bg-surface-2 transition-colors';
        $filtroEstado = fn($e) => route('usuarios.index', array_filter(['estado' => $e]));
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 mb-6">
        <a href="{{ route('usuarios.index') }}" class="{{ $tarjeta }} col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Total usuarios</span>
                <span class="w-8 h-8 rounded-lg bg-brand-blue/20 text-brand-blue flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-ink">{{ $resumen['total'] }}</p>
            <p class="mt-1 text-[11px] text-muted leading-snug">
                @foreach ($nombresRol as $clave => $nombre)
                    @if (!empty($resumen['por_rol'][$clave]))
                        {{ $resumen['por_rol'][$clave] }} {{ strtolower($nombre) }}{{ !$loop->last ? ' ·' : '' }}
                    @endif
                @endforeach
            </p>
        </a>

        <a href="{{ $filtroEstado('ACTIVO') }}" class="{{ $tarjeta }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Activos</span>
                <span class="w-8 h-8 rounded-lg bg-brand-green/20 text-brand-green flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-ink">{{ $resumen['activos'] }}</p>
            <p class="mt-1 text-[11px] text-muted">Pueden iniciar sesión</p>
        </a>

        <a href="{{ $filtroEstado('INACTIVO') }}" class="{{ $tarjeta }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Inactivos</span>
                <span class="w-8 h-8 rounded-lg bg-red-500/20 text-red-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-ink">{{ $resumen['inactivos'] }}</p>
            <p class="mt-1 text-[11px] text-muted">Historial conservado</p>
        </a>

        <a href="{{ route('usuarios.solicitudes.index') }}" class="{{ $tarjeta }} col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Solicitudes pendientes</span>
                <span class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-ink">{{ $resumen['solicitudes'] }}</p>
            <p class="mt-1 text-[11px] text-muted">{{ $resumen['solicitudes'] > 0 ? 'Esperan tu revisión' : 'Todo al día' }}</p>
        </a>
    </div>

    {{-- Barra de Filtros --}}
    <form method="GET" action="{{ route('usuarios.index') }}"
        class="mb-6 p-4 rounded-2xl border border-line bg-surface-2/60 shadow-soft grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-center">

        @if (request()->filled('orden'))
        <input type="hidden" name="orden" value="{{ $orden }}">
        <input type="hidden" name="dir" value="{{ $dir }}">
        @endif

        {{-- Input de Búsqueda --}}
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                placeholder="Nombre, usuario o correo..."
                class="w-full pl-9 pr-3 py-2 rounded-lg bg-surface-2 border border-line text-ink placeholder:text-muted text-xs focus:border-brand-green/50 focus:ring-brand-green/20">
        </div>

        {{-- Select Rol --}}
        <div>
            <select name="rol" class="w-full px-3 py-2 rounded-lg bg-surface-2 border border-line text-ink text-xs focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">Todos los roles</option>
                @foreach (['GERENTE','ADMINISTRADOR','VENDEDOR','SERVICIO_TECNICO'] as $r)
                <option value="{{ $r }}" @selected(request('rol')===$r)>{{ $r }}</option>
                @endforeach
            </select>
        </div>

        {{-- Select Sucursal --}}
        <div>
            <select name="id_sucursal" class="w-full px-3 py-2 rounded-lg bg-surface-2 border border-line text-ink text-xs focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">Todas las sucursales</option>
                @foreach ($sucursales as $s)
                <option value="{{ $s->id_sucursal }}"
                    @selected((string) request('id_sucursal')===(string) $s->id_sucursal)>
                    {{ $s->nombre }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Select Estado --}}
        <div>
            <select name="estado" class="w-full px-3 py-2 rounded-lg bg-surface-2 border border-line text-ink text-xs focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">Todos los estados</option>
                <option value="ACTIVO" @selected(request('estado')==='ACTIVO' )>Activo</option>
                <option value="INACTIVO" @selected(request('estado')==='INACTIVO' )>Inactivo</option>
            </select>
        </div>

        {{-- Filtro: cuentas bloqueadas por intentos fallidos --}}
        <div class="flex items-end gap-2">
            <input type="checkbox" name="bloqueados" id="bloqueados" value="1"
                @checked(request()->boolean('bloqueados'))
                class="w-4 h-4 text-brand-green rounded border-line bg-surface-2 focus:ring-brand-green">
            <label for="bloqueados" class="text-xs text-muted font-medium cursor-pointer">Bloqueadas</label>
        </div>

        {{-- Filtro: cuentas sin correo ni celular (no pueden recuperar clave) --}}
        <div class="flex items-end gap-2">
            <input type="checkbox" name="sin_recuperacion" id="sin_recuperacion" value="1"
                @checked(request()->boolean('sin_recuperacion'))
                class="w-4 h-4 text-brand-blue rounded border-line bg-surface-2 focus:ring-brand-blue">
            <label for="sin_recuperacion" class="text-xs text-muted font-medium cursor-pointer">Sin recuperación</label>
        </div>

        {{-- Botón Filtrar --}}
        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 py-2 px-4 bg-brand-green text-white text-xs font-bold rounded-xl border border-brand-green/50 hover:bg-brand-green-dark transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>Filtrar</span>
            </button>
            @if(request()->anyFilled(['buscar', 'rol', 'id_sucursal', 'estado']))
            <a href="{{ route('usuarios.index') }}"
                class="px-3 py-2 rounded-lg bg-surface-2 text-ink hover:text-brand-green border border-line transition-colors flex items-center justify-center" title="Limpiar filtros">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
            @endif
        </div>
    </form>

    {{-- Contenedor de la Tabla --}}
    <div class="bg-surface rounded-2xl border border-line shadow-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-sm text-left">
                <thead class="bg-surface-2 text-muted text-xs uppercase tracking-wider">
                    @php
                        // Encabezados ordenables: 1er clic asc, 2º clic desc. Conserva los filtros activos.
                        $columnas = [
                            'nombre_completo'         => 'Nombre',
                            'nombre_usuario'          => 'Usuario',
                            'correo'                  => 'Correo',
                            'celular'                 => 'Celular',
                            'nombre_rol'              => 'Rol',
                            'sucursal'                => 'Sucursal',
                            'porcentaje_comision_pct' => 'Comisión',
                            'estado'                  => 'Estado',
                        ];
                        $urlOrden = fn($col) => route('usuarios.index', array_merge(
                            request()->except(['page', 'orden', 'dir']),
                            ['orden' => $col, 'dir' => ($orden === $col && $dir === 'asc') ? 'desc' : 'asc']
                        ));
                    @endphp
                    <tr>
                        @foreach ($columnas as $col => $titulo)
                        <th scope="col" class="px-5 py-3.5 font-semibold" aria-sort="{{ $orden === $col ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                            <a href="{{ $urlOrden($col) }}"
                                class="group inline-flex items-center gap-1.5 uppercase tracking-wider transition-colors {{ $orden === $col ? 'text-brand-blue' : 'hover:text-ink' }}"
                                title="Ordenar por {{ strtolower($titulo) }}">
                                <span>{{ $titulo }}</span>
                                @if ($orden === $col)
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $dir === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                </svg>
                                @else
                                <svg class="w-3.5 h-3.5 opacity-0 group-hover:opacity-60 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                                @endif
                            </a>
                        </th>
                        @endforeach
                        <th scope="col" class="px-5 py-3.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-muted">
                    @forelse ($usuarios as $u)
                    <tr class="hover:bg-surface-2 transition-colors">
                        <td class="px-5 py-4 font-medium text-ink">
                            {{ $u->nombre_completo }}
                            @if ($u->debe_cambiar_contrasena)
                            <span class="ml-1.5 inline-flex px-1.5 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider border border-amber-500/30 bg-amber-500/20 text-amber-500" title="Debe cambiar su contraseña al ingresar">Clave temporal</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-muted">{{ $u->nombre_usuario }}</td>
                        <td class="px-5 py-4 text-muted">{{ $u->correo ?? '—' }}</td>
                        <td class="px-5 py-4 text-muted">{{ $u->celular ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border border-line bg-surface-2 text-muted">
                                {{ $u->nombre_rol }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-muted">{{ $u->sucursal?->nombre ?? '—' }}</td>
                        <td class="px-5 py-4 font-medium text-ink">{{ number_format((float) $u->porcentaje_comision_pct, 2) }}%</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $u->estado === 'ACTIVO' ? 'border border-brand-green/20 bg-brand-green/10 text-brand-green' : 'border border-red-500/30 bg-red-500/10 text-red-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $u->estado === 'ACTIVO' ? 'bg-brand-green' : 'bg-red-500' }}"></span>
                                {{ $u->estado }}
                            </span>
                        </td>
                        {{-- Bloqueo por intentos fallidos --}}
                        <td class="px-5 py-4 text-center">
                            @if ($u->estaBloqueado())
                            <span class="inline-flex px-2 py-1 rounded-lg text-[10px] font-semibold uppercase tracking-wider border border-red-500/30 bg-red-500/10 text-red-500" title="Bloqueado hasta {{$u->minutosDeBloqueo()}} min">
                                {{ $u->minutosDeBloqueo() }} min
                            </span>
                            @else
                            <span class="text-[10px] text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('usuarios.edit', $u) }}"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-brand-blue hover:text-brand-blue-dark transition-colors">
                                Editar
                            </a>

                            @if ($u->id_usuario !== auth()->id())
                            @if (auth()->user()->esGerente() || !$u->esGerente())
                            <form action="{{ route('usuarios.resetPassword', $u) }}" method="POST" class="inline"
                                onsubmit="return confirm('¿Restablecer la contraseña de {{ addslashes($u->nombre_completo) }}?\\nSe generará una contraseña temporal y deberá cambiarla al iniciar sesión.');">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="orden" value="{{ $orden }}">
                                <input type="hidden" name="dir" value="{{ $dir }}">
                                @foreach (['buscar', 'rol', 'estado', 'id_sucursal', 'page'] as $f)
                                    @if (request()->filled($f))
                                    <input type="hidden" name="{{ $f }}" value="{{ request($f) }}">
                                    @endif
                                @endforeach
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-amber-500 hover:text-amber-400 transition-colors cursor-pointer"
                                    title="Genera una contraseña temporal">
                                    Restablecer clave
                                </button>
                            </form>
                            @endif

                            {{-- Desbloquear cuenta (solo si está bloqueada y no es el actor) --}}
                            @if ($u->id_usuario !== auth()->id() && $u->estaBloqueado())
                            <form action="{{ route('usuarios.desbloquear', $u) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="orden" value="{{ $orden }}">
                                <input type="hidden" name="dir" value="{{ $dir }}">
                                @foreach (['buscar', 'rol', 'estado', 'id_sucursal', 'bloqueados', 'sin_recuperacion', 'page'] as $f)
                                    @if (request()->filled($f))
                                    <input type="hidden" name="{{ $f }}" value="{{ request($f) }}">
                                    @endif
                                @endforeach
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-brand-green hover:text-brand-green-dark transition-colors cursor-pointer"
                                    title="Levantar el bloqueo de intentos fallidos">
                                    Desbloquear
                                </button>
                            </form>
                            @endif

                            <form action="{{ route('usuarios.toggleEstado', $u) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs font-semibold {{ $u->estado === 'ACTIVO' ? 'text-red-500 hover:text-red-400' : 'text-brand-green hover:text-brand-green-dark' }} transition-colors cursor-pointer">
                                    {{ $u->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-muted">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="text-xs font-medium text-muted">No hay usuarios registrados que coincidan con la búsqueda.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-6">
        {{ $usuarios->links() }}
    </div>
</div>

@if (session('password_temporal'))
<script>
    document.getElementById('tmp-copy')?.addEventListener('click', async function () {
        const el = document.getElementById('tmp-password');
        const txt = document.getElementById('tmp-copy-text');
        let ok = false;
        try {
            await navigator.clipboard.writeText(el.textContent.trim());
            ok = true;
        } catch (e) {
            const r = document.createRange();
            r.selectNodeContents(el);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(r);
            try { ok = document.execCommand('copy'); } catch (e2) { ok = false; }
        }
        txt.textContent = ok ? '¡Copiada!' : 'Selecciona y copia';
        setTimeout(() => { txt.textContent = 'Copiar'; }, 1800);
    });
</script>
@endif
@endsection
