<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EwiflorWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-slate-800 bg-slate-950 flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Decoraciones de fondo con efectos Glow/Gradients -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/30 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Contenedor Principal con animación de entrada -->
    <div class="relative w-full max-w-md bg-slate-900/80 backdrop-blur-xl border border-slate-800 p-8 rounded-2xl shadow-2xl shadow-black/50 transform transition-all duration-300 animate-[fade-in_0.5s_ease-out]">

        <!-- Encabezado & Identidad -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white shadow-lg shadow-blue-500/30 mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 4h4m-4-8h4m-4 4h4" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Ewiflor Importaciones</h1>
            <p class="text-sm text-slate-400 mt-1">Ingresa tus credenciales para acceder al sistema</p>
        </div>

        <!-- Manejo de Errores con animación Shake y diseño pulido -->
        @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-950/50 border border-red-500/30 text-red-200 text-sm animate-[shake_0.4s_ease-in-out]">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-semibold block text-red-300">Error de autenticación</span>
                    <p class="mt-0.5 text-xs text-red-300/80">{{ $errors->first() }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Campo Usuario -->
            <div>
                <label for="nombre_usuario" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Usuario
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input id="nombre_usuario" type="text" name="nombre_usuario" value="{{ old('nombre_usuario') }}" required autofocus
                        placeholder="Nombre de usuario"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-800/60 border border-slate-700/80 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-200">
                </div>
            </div>

            <!-- Campo Contraseña -->
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Contraseña
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input id="password" type="password" name="password" required
                        placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-800/60 border border-slate-700/80 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-200">
                </div>
            </div>

            <!-- Botón de Envío -->
            <button type="submit"
                class="w-full py-3 px-4 mt-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium text-sm rounded-xl shadow-lg shadow-blue-600/25 hover:shadow-blue-600/40 focus:outline-none focus:ring-2 focus:ring-blue-500/40 active:scale-[0.98] transition-all duration-150 flex items-center justify-center gap-2">
                <span>Ingresar al Sistema</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>
    </div>

</body>

</html>