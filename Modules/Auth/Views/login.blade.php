<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EwiflorWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased bg-slate-950 flex items-center justify-center p-4 relative overflow-y-auto select-none">

    <!-- Capa sutil de fondo para profundidad visual (Material Elevation Surface) -->
    <div class="fixed inset-0 bg-slate-900/40 pointer-events-none"></div>

    <!-- Contenedor Principal (Tarjetas e integración Material Design 3) -->
    <div class="relative w-full max-w-md bg-slate-900 border border-slate-800/80 rounded-2xl p-8 shadow-2xl transition-all duration-300 ease-out opacity-100 animate-[fade-in_0.4s_ease-out] my-auto">

        <!-- Botón de Regresar a la Página Principal (Top Bar Left) -->
        <div class="flex items-center justify-between mb-4">
            <a href="/"
                class="inline-flex items-center gap-2 text-xs font-medium text-slate-400 hover:text-white transition-colors duration-200 py-1 px-2.5 rounded-lg bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Inicio</span>
            </a>
            <!-- Indicador Accent Material -->
            <div class="w-10 h-1 bg-blue-500 rounded-full opacity-80"></div>
        </div>

        <!-- Aviso de solicitud enviada -->
        @if (session('solicitud_ok'))
        <div class="mb-6 p-3.5 rounded-xl bg-emerald-950/40 border border-emerald-500/40 text-sm animate-[fade-in_0.2s_ease-in]" role="status">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-semibold block text-xs uppercase tracking-wider text-emerald-400">Solicitud enviada</span>
                    <p class="mt-0.5 text-xs text-emerald-200/90">{{ session('solicitud_ok') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- SECCIÓN SUPERIOR: Solicitud de Cuenta (se abre sola si hay errores) -->
        @php($errSol = $errors->getBag('solicitud'))
        @php($campoSol = 'w-full px-3 py-2 bg-slate-900 border border-slate-700/60 rounded-lg text-slate-100 placeholder-slate-600 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500')
        <details class="group mb-8 bg-slate-950/60 border border-slate-800 rounded-xl overflow-hidden transition-all duration-200" @if ($errSol->any()) open @endif>
            <summary class="flex items-center justify-between p-3.5 cursor-pointer list-none select-none hover:bg-slate-800/40 transition-colors">
                <div class="flex items-center gap-2.5 text-xs font-semibold uppercase tracking-wider text-slate-300">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>¿No tienes cuenta? Solicita una</span>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </summary>

            <!-- Formulario de Petición de Cuenta -->
            <form method="POST" action="{{ route('solicitudes.store') }}" class="p-4 pt-2 border-t border-slate-800 space-y-3.5" novalidate>
                @csrf

                <!-- Honeypot anti-bots: debe quedar vacío (oculto para personas) -->
                <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                    <label>Sitio web <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label>
                </div>

                <div>
                    <label for="sol_nombre" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Nombre Completo</label>
                    <input id="sol_nombre" type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" maxlength="100" required
                        placeholder="Ej. Carlos Mendoza" class="{{ $campoSol }}">
                    @if ($errSol->has('nombre_completo')) <p class="text-[11px] text-red-400 mt-1">{{ $errSol->first('nombre_completo') }}</p> @endif
                </div>

                <div>
                    <label for="sol_correo" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Correo Electrónico</label>
                    <input id="sol_correo" type="email" name="correo" value="{{ old('correo') }}" maxlength="100" required
                        placeholder="correo@empresa.com" class="{{ $campoSol }}">
                    @if ($errSol->has('correo')) <p class="text-[11px] text-red-400 mt-1">{{ $errSol->first('correo') }}</p> @endif
                </div>

                <div>
                    <label for="sol_celular" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Número de Celular</label>
                    <input id="sol_celular" type="tel" name="celular" value="{{ old('celular') }}" maxlength="20" required
                        placeholder="+591 70000000" class="{{ $campoSol }}">
                    @if ($errSol->has('celular')) <p class="text-[11px] text-red-400 mt-1">{{ $errSol->first('celular') }}</p> @endif
                </div>

                <div>
                    <label for="sol_motivo" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Motivo de la Solicitud</label>
                    <textarea id="sol_motivo" name="motivo" rows="2" maxlength="500" required
                        placeholder="Especifica el área o rol requerido..." class="{{ $campoSol }} resize-none">{{ old('motivo') }}</textarea>
                    @if ($errSol->has('motivo')) <p class="text-[11px] text-red-400 mt-1">{{ $errSol->first('motivo') }}</p> @endif
                </div>

                <button type="submit"
                    class="w-full py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-xs rounded-lg border border-slate-700/80 transition-colors duration-150 cursor-pointer">
                    Enviar Solicitud
                </button>
            </form>
        </details>

        <!-- Encabezado & Identidad -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-600 text-white shadow-md mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 4h4m-4-8h4m-4 4h4" />
                </svg>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-slate-100">Ewiflor Importaciones</h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Ingresa tus credenciales para acceder al sistema</p>
        </div>

        <!-- Manejo de Errores -->
        @if ($errors->any())
        <div class="mb-6 p-3.5 rounded-xl bg-red-950/40 border border-red-500/40 text-red-200 text-sm opacity-95 transition-opacity duration-200 animate-[fade-in_0.2s_ease-in]">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-semibold block text-xs uppercase tracking-wider text-red-400">Error de autenticación</span>
                    <p class="mt-0.5 text-xs text-red-300/90">{{ $errors->first() }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Formulario de Login Principal -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Campo Usuario -->
            <div>
                <label for="nombre_usuario" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Usuario
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input id="nombre_usuario" type="text" name="nombre_usuario" value="{{ old('nombre_usuario') }}" required autofocus
                        placeholder="Nombre de usuario"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-800 border border-slate-700/70 rounded-xl text-slate-100 placeholder-slate-500 text-sm opacity-90 focus:opacity-100 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition-all duration-200">
                </div>
            </div>

            <!-- Campo Contraseña -->
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Contraseña
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>

                    <input id="password" type="password" name="password" required
                        placeholder="••••••••"
                        class="w-full pl-10 pr-12 py-2.5 bg-slate-800 border border-slate-700/70 rounded-xl text-slate-100 placeholder-slate-500 text-sm opacity-90 focus:opacity-100 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition-all duration-200">

                    <!-- Botón Mostrar / Ocultar Contraseña -->
                    <button type="button" onclick="togglePasswordVisibility()"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none transition-colors cursor-pointer"
                        title="Ver contraseña">
                        <!-- Icono Ojo Abierto (Por defecto oculto) -->
                        <svg id="eye-open" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Icono Ojo Cerrado (Por defecto visible) -->
                        <svg id="eye-closed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Botón de Envío -->
            <button type="submit"
                class="w-full py-3 px-4 mt-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 active:scale-[0.99] opacity-95 hover:opacity-100 transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer">
                <span>Ingresar al Sistema</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            }
        }
    </script>

</body>

</html>