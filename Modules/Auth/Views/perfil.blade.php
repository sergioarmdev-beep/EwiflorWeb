@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 text-slate-100">

    <!-- Encabezado de la Sección -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-white">Mi perfil</h1>
        <p class="text-sm text-slate-300 mt-0.5">Consulta tus datos de acceso y mantén tu contraseña segura</p>
    </div>

    @if ($forzado)
    <!-- Aviso: cambio de contraseña obligatorio -->
    <div class="mb-5 p-4 rounded-2xl bg-amber-950/30 border border-amber-500/40 text-sm">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-amber-300">Cambio de contraseña obligatorio</span>
                <p class="mt-1 text-xs text-amber-100/80">Un administrador restableció tu contraseña. Por seguridad, define una nueva para poder continuar usando el sistema.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Datos de la cuenta (solo lectura) -->
    <div class="mb-5 bg-slate-900 border border-slate-800/80 rounded-2xl p-6 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 shrink-0 rounded-xl bg-blue-600 text-white shadow-md flex items-center justify-center text-lg font-bold">
                {{ mb_strtoupper(mb_substr($usuario->nombre_completo, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <h2 class="text-base font-bold text-slate-100 truncate">{{ $usuario->nombre_completo }}</h2>
                <p class="text-xs font-mono text-slate-400">{{ $usuario->nombre_usuario }}</p>
            </div>
        </div>

        <dl class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4 pt-5 border-t border-slate-800">
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Rol</dt>
                <dd class="mt-1">
                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 border border-slate-700/50 text-slate-300">{{ $usuario->nombre_rol }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sucursal</dt>
                <dd class="mt-1 text-sm text-slate-300">{{ $usuario->sucursal?->nombre ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Correo</dt>
                <dd class="mt-1 text-sm text-slate-300 truncate">{{ $usuario->correo ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <!-- Cambiar contraseña -->
    <form action="{{ route('perfil.password') }}" method="POST"
        class="bg-slate-900 border border-sky-500/40 rounded-2xl p-6 shadow-xl space-y-5">
        @csrf
        @method('PUT')

        <div>
            <h2 class="text-base font-bold text-slate-100">Cambiar contraseña</h2>
            <p class="text-xs text-slate-400 mt-0.5">Necesitas tu contraseña actual para confirmar el cambio.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Contraseña actual -->
            <div class="md:col-span-2">
                <label for="password_actual" class="block text-xs font-semibold uppercase tracking-wider text-slate-200 mb-1.5">Contraseña actual *</label>
                <div class="relative">
                    <input type="password" name="password_actual" id="password_actual" required autocomplete="current-password"
                        placeholder="••••••••"
                        class="mt-1 w-full px-3.5 py-2.5 pr-12 bg-slate-800 border border-sky-500/40 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-500/30 transition-all">
                    <button type="button" onclick="toggleFieldVisibility('password_actual', 'eye-open-0', 'eye-closed-0')"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none transition-colors cursor-pointer"
                        title="Ver contraseña">
                        <svg id="eye-open-0" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eye-closed-0" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                @error('password_actual') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Nueva contraseña + confirmación + medidor + generador -->
            @include('auth::partials.password-fields', [
                'requerido' => true,
                'etiqueta'  => 'Nueva contraseña',
            ])
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700">
            @unless ($forzado)
            <a href="{{ route('dashboard') }}"
                class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-sky-500/40 transition-colors cursor-pointer">
                Cancelar
            </a>
            @endunless
            <button type="submit"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-150 active:scale-[0.99] cursor-pointer flex items-center gap-2 border border-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Actualizar contraseña</span>
            </button>
        </div>
    </form>
</div>
@endsection
