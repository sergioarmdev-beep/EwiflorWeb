<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EwiflorWeb - Sistema de Gestión</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Script inicial para prevenir parpadeo de tema -->
    <script>
        if (localStorage.theme === 'light') {
            document.documentElement.classList.remove('dark', 'bg-slate-950', 'text-slate-100');
            document.documentElement.classList.add('bg-gray-100', 'text-gray-900');
        } else {
            document.documentElement.classList.add('dark', 'bg-slate-950', 'text-slate-100');
        }
    </script>
</head>

<body class="h-full font-sans antialiased overflow-hidden bg-slate-950 dark:bg-slate-950 text-slate-100 transition-colors duration-200">
    <div class="flex h-screen bg-transparent relative overflow-hidden">

        <!-- Overlay para móviles cuando el sidebar esté abierto -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 z-30 lg:hidden hidden transition-opacity duration-300"></div>

        <!-- Sidebar Colapsable, Semitransparente y Animado -->
        <aside id="sidebar" class="fixed lg:relative inset-y-0 left-0 z-40 w-64 bg-slate-900/90 dark:bg-slate-900/90 bg-white/90 backdrop-blur-xl border-r border-slate-800/80 dark:border-slate-800/80 border-gray-200 flex flex-col transition-all duration-300 ease-in-out shadow-2xl shrink-0">

            <!-- Logo / Cabecera Sidebar -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800/80 dark:border-slate-800/80 border-gray-200">
                <span class="text-lg font-bold tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    Ewiflor
                </span>
                <!-- Botón de cerrar sidebar en móvil -->
                <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-xl focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navegación con Botones Ovalados (rounded-xl) -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-600/20 text-blue-500 border border-blue-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('catalogo.productos.index') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium transition-colors {{ request()->routeIs('catalogo.productos.*') ? 'bg-blue-600/20 text-blue-500 border border-blue-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Productos
                </a>

                <a href="{{ route('catalogo.marcas.index') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium transition-colors {{ request()->routeIs('catalogo.marcas.*') ? 'bg-blue-600/20 text-blue-500 border border-blue-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Marcas
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium text-slate-500 hover:bg-slate-800/30 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Clientes (Próximamente)
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium text-slate-500 hover:bg-slate-800/30 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Ventas (Próximamente)
                </a>

                @if (auth()->user()?->puedeGestionarUsuarios())
                <a href="{{ route('usuarios.index') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium transition-colors {{ request()->routeIs('usuarios.*') ? 'bg-blue-600/20 text-blue-500 border border-blue-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Usuarios
                </a>
                @endif

                @if (in_array(auth()->user()?->nombre_rol, ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'], true))
                <a href="{{ route('precios.tipo-cambio.index') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-medium transition-colors {{ request()->routeIs('precios.tipo-cambio.*') ? 'bg-blue-600/20 text-blue-500 border border-blue-500/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Tipo de cambio
                </a>
                @endif
            </nav>

            <!-- Pie de Sidebar (Botón Regresar al Inicio y Logout) -->
            <div class="p-4 border-t border-slate-800/80 dark:border-slate-800/80 border-gray-200 bg-slate-950/30 space-y-2">
                <!-- Botón de Regresar a la Página Principal -->
                <a href="/"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-medium text-slate-300 hover:text-white bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Página Principal</span>
                </a>

                <!-- Botón de Cerrar Sesión -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl text-xs font-medium text-red-400 hover:bg-red-950/30 hover:text-red-300 border border-red-500/20 transition-colors cursor-pointer">
                        <span class="truncate">Cerrar Sesión</span>
                        <span class="text-[10px] text-slate-400 font-mono">({{ Auth::user()->nombre_usuario }})</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area (Se expande fluidamente al ocultar el aside) -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-transparent transition-all duration-300 ease-in-out">
            <!-- Header Superior con Botón Hamburguesa y Selector de Tema -->
            <header class="bg-slate-900/60 dark:bg-slate-900/60 bg-white/60 backdrop-blur-md border-b border-slate-800/80 dark:border-slate-800/80 border-gray-200 h-16 flex items-center justify-between px-6 shrink-0 z-20 transition-colors">
                <div class="flex items-center gap-4">
                    <!-- Botón Hamburguesa (Oculta/Muestra el aside con animación y expande el contenido) -->
                    <button onclick="toggleSidebar()" class="p-2 rounded-xl bg-slate-800/80 dark:bg-slate-800/80 bg-gray-200 text-slate-300 dark:text-slate-300 text-gray-700 hover:text-white dark:hover:text-white hover:bg-slate-800 border border-slate-700/60 dark:border-slate-700/60 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all cursor-pointer" title="Alternar menú lateral">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold tracking-tight">@yield('title', 'Dashboard')</h1>
                </div>

                <!-- Botón de Modo Oscuro / Claro Ovalado -->
                <button onclick="toggleTheme()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800/80 dark:bg-slate-800/80 bg-gray-200 border border-slate-700/60 dark:border-slate-700/60 border-gray-300 text-xs font-medium hover:bg-slate-800 dark:hover:bg-slate-800 hover:text-white transition-all cursor-pointer" title="Cambiar Tema">
                    <!-- Icono Sol (Visible en modo oscuro) -->
                    <svg id="theme-icon-light" class="w-4 h-4 text-amber-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Icono Luna (Visible en modo claro) -->
                    <svg id="theme-icon-dark" class="w-4 h-4 text-blue-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span id="theme-text" class="hidden sm:inline">Tema</span>
                </button>
            </header>

            <!-- Contenido Dinámico de la Página Centrado y Autocompletable -->
            <div class="flex-1 overflow-y-auto p-6 bg-transparent flex justify-center">
                <div class="w-full max-w-7xl transition-all duration-300">
                    @if(session('success'))
                    <div class="mb-4 p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/40 text-emerald-200 text-sm flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-4 p-4 rounded-xl bg-red-950/40 border border-red-500/40 text-red-200 text-sm flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts de Control (Sidebar Colapsable, Expansión de Contenido y Tema) -->
    <script>
        // Control del menú lateral (Hamburguesa) con desplazamiento de contenido
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            // En pantallas grandes oculta/muestra el ancho traduciéndolo hacia la izquierda
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Inicializar iconos del tema según el estado actual al cargar la página
        document.addEventListener("DOMContentLoaded", () => {
            const isDark = document.documentElement.classList.contains('dark');
            updateThemeIcons(isDark);
        });

        // Control de Tema Oscuro / Claro
        function toggleTheme() {
            const root = document.documentElement;
            const isDark = root.classList.contains('dark');

            if (isDark) {
                // Cambiar a modo Claro
                root.classList.remove('dark', 'bg-slate-950', 'text-slate-100');
                root.classList.add('bg-gray-100', 'text-gray-900');
                localStorage.theme = 'light';
                updateThemeIcons(false);
            } else {
                // Cambiar a modo Oscuro
                root.classList.remove('bg-gray-100', 'text-gray-900');
                root.classList.add('dark', 'bg-slate-950', 'text-slate-100');
                localStorage.theme = 'dark';
                updateThemeIcons(true);
            }
        }

        function updateThemeIcons(isDark) {
            const iconLight = document.getElementById('theme-icon-light');
            const iconDark = document.getElementById('theme-icon-dark');

            if (isDark) {
                iconLight.classList.remove('hidden'); // Muestra sol para poder cambiar a claro
                iconDark.classList.add('hidden');
            } else {
                iconLight.classList.add('hidden');
                iconDark.classList.remove('hidden'); // Muestra luna para poder cambiar a oscuro
            }
        }
    </script>
</body>

</html>