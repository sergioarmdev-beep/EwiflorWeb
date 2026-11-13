<!DOCTYPE html>
<html lang="es" class="h-full dark" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | EwiflorWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        (() => {
            let theme = 'dark';
            try {
                theme = localStorage.getItem('theme') || 'dark';
            } catch (error) {
                /* Oscuro por defecto. */ }
            document.documentElement.classList.toggle('dark', theme !== 'light');
        })();
    </script>
</head>

<body class="h-full overflow-hidden bg-slate-50 text-slate-900 antialiased transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100">
    @php
    $usuarioActual = auth()->user();
    $rolActual = $usuarioActual?->nombre_rol;
    $puedeVender = in_array($rolActual, ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'], true);
    $puedeGestionarUsuarios = $usuarioActual?->puedeGestionarUsuarios() ?? false;
    // Preparado para cuando se incorpore la columna foto_perfil al usuario.
    $fotoPerfil = $usuarioActual?->foto_perfil;
    $nombreAvatar = trim((string) ($usuarioActual?->nombre_usuario ?? 'U'));
    $inicialAvatar = strtoupper(substr($nombreAvatar, 0, 1));
    @endphp

    <div id="app-shell" class="relative flex h-screen overflow-hidden bg-slate-950">
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden" data-sidebar-close></div>

        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white shadow-2xl transition-all duration-300 ease-in-out dark:border-slate-800 dark:bg-slate-900 lg:relative lg:translate-x-0 lg:shadow-none">
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 px-5 dark:border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-600 text-white shadow-lg shadow-sky-600/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 7.8 7.5 4.4 7.5-4.4M12 12.2V21" />
                        </svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-base font-bold tracking-tight text-slate-900 dark:text-white">EwiflorWeb</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Gestión empresarial</span>
                    </span>
                </a>
                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white lg:hidden" data-sidebar-close aria-label="Cerrar menú">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Navegación principal">
                <div>
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">Principal</p>
                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-sky-600 dark:text-sky-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="3" width="8" height="8" rx="2" />
                            <rect x="13" y="3" width="8" height="5" rx="2" />
                            <rect x="13" y="10" width="8" height="11" rx="2" />
                            <rect x="3" y="13" width="8" height="8" rx="2" />
                        </svg>
                        Dashboard
                    </a>
                </div>

                <div>
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">Operaciones</p>

                    <details class="nav-group group" {{ request()->routeIs('catalogo.productos.*', 'catalogo.marcas.*') ? 'open' : '' }}>
                        <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80 [&::-webkit-details-marker]:hidden">
                            <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 9 4.5v9L12 21l-9-4.5v-9L12 3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3.5 7.7 8.5 4.5 8.5-4.5M12 12.2V21" />
                            </svg>
                            <span class="flex-1">Catálogo</span>
                            <svg class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="ml-5 mt-1 space-y-1 border-l border-slate-200 pl-4 dark:border-slate-700">
                            <a href="{{ route('catalogo.productos.index') }}" class="nav-link {{ request()->routeIs('catalogo.productos.*') ? 'nav-link-active' : '' }}">Productos</a>
                            <a href="{{ route('catalogo.marcas.index') }}" class="nav-link {{ request()->routeIs('catalogo.marcas.*') ? 'nav-link-active' : '' }}">Marcas</a>
                        </div>
                    </details>

                    <details class="nav-group group mt-1" {{ request()->routeIs('inventario.existencias.*', 'inventario.movimientos.*') ? 'open' : '' }}>
                        <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80 [&::-webkit-details-marker]:hidden">
                            <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 7.8 12 12l8.5-4.2M12 12v9" />
                            </svg>
                            <span class="flex-1">Inventario</span>
                            <svg class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="ml-5 mt-1 space-y-1 border-l border-slate-200 pl-4 dark:border-slate-700">
                            <a href="{{ route('inventario.existencias.index') }}" class="nav-link {{ request()->routeIs('inventario.existencias.*') ? 'nav-link-active' : '' }}">Existencias</a>
                            <a href="{{ route('inventario.movimientos.index') }}" class="nav-link {{ request()->routeIs('inventario.movimientos.*') ? 'nav-link-active' : '' }}">Movimientos</a>
                        </div>
                    </details>

                    @if ($puedeVender)
                    <details class="nav-group group mt-1" {{ request()->routeIs('clientes.*', 'cotizaciones.*', 'ventas.*') ? 'open' : '' }}>
                        <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80 [&::-webkit-details-marker]:hidden">
                            <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 1.9-1.4L22 8H6" />
                                <circle cx="10" cy="20" r="1" />
                                <circle cx="18" cy="20" r="1" />
                            </svg>
                            <span class="flex-1">Comercial</span>
                            <svg class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="ml-5 mt-1 space-y-1 border-l border-slate-200 pl-4 dark:border-slate-700">
                            <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'nav-link-active' : '' }}">Clientes</a>
                            <a href="{{ route('cotizaciones.index') }}" class="nav-link {{ request()->routeIs('cotizaciones.*') ? 'nav-link-active' : '' }}">Cotizaciones</a>
                            <a href="{{ route('ventas.index') }}" class="nav-link {{ request()->routeIs('ventas.*') ? 'nav-link-active' : '' }}">Ventas</a>
                        </div>
                    </details>
                    @endif
                </div>

                @if ($puedeGestionarUsuarios || in_array($rolActual, ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'], true))
                <div>
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">Administración</p>
                    @if ($puedeGestionarUsuarios)
                    <a href="{{ route('usuarios.index') }}" class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('usuarios.*') ? 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6" />
                        </svg>
                        Usuarios
                    </a>
                    @endif
                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'], true))
                    <a href="{{ route('precios.tipo-cambio.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('precios.tipo-cambio.*') ? 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h14m0 0-3-3m3 3-3 3M17 17H3m0 0 3 3m-3-3 3-3" />
                        </svg>
                        Tipo de cambio
                    </a>
                    @endif
                </div>
                @endif
            </nav>

            <div class="shrink-0 border-t border-slate-200 p-3 dark:border-slate-800">
                <a href="/" class="mb-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                    <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                    </svg>
                    Página principal
                </a>

                <div class="flex items-center gap-3 rounded-xl bg-slate-100 p-2.5 dark:bg-slate-800/70">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-sky-100 text-sm font-bold text-sky-700 ring-1 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/20">
                        @if ($fotoPerfil)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($fotoPerfil) }}" alt="Foto de perfil" class="h-full w-full object-cover">
                        @else
                        {{ $inicialAvatar }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $usuarioActual?->nombre_usuario ?? 'Usuario' }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $rolActual ?? 'Sin rol' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-500/10 dark:hover:text-red-300" title="Cerrar sesión" aria-label="Cerrar sesión">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <header class="z-20 flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white/85 px-4 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/75 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button id="sidebar-toggle" type="button" class="rounded-xl border border-slate-700 bg-slate-800 p-2 text-slate-300 transition hover:bg-slate-700" aria-label="Alternar menú" aria-controls="sidebar" aria-expanded="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h1 class="truncate text-base font-semibold tracking-tight text-slate-900 dark:text-white">@yield('title', 'Dashboard')</h1>
                        @hasSection('subtitle')
                        <p class="hidden truncate text-xs text-slate-500 dark:text-slate-400 sm:block">@yield('subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button id="theme-toggle" type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:bg-slate-700" title="Cambiar tema" aria-label="Cambiar tema">
                        <svg id="theme-sun" class="hidden h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
                        </svg>
                        <svg id="theme-moon" class="h-4 w-4 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5 8.5 8.5 0 1 0 20.5 15.5Z" />
                        </svg>
                        <span class="hidden sm:inline">Tema</span>
                    </button>
                    <div class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-sky-100 text-sm font-bold text-sky-700 ring-1 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/20" title="{{ $usuarioActual?->nombre_usuario }}">
                        @if ($fotoPerfil)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($fotoPerfil) }}" alt="Foto de perfil" class="h-full w-full object-cover">
                        @else
                        {{ $inicialAvatar }}
                        @endif
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto bg-slate-50 px-4 py-5 dark:bg-slate-950 sm:px-6 sm:py-6">
                <div class="mx-auto w-full max-w-7xl">
                    @if (session('success'))
                    <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 8v5m0 3h.01" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <style>
        /* El tema oscuro es el aspecto predeterminado de la aplicación. */
        html,
        body {
            background: #020617;
            color: #f1f5f9;
        }

        #app-shell {
            background: #020617;
        }

        /* Sidebar: cierre real en escritorio y desplazamiento en móvil. */
        #sidebar {
            flex: 0 0 16rem;
            width: 16rem;
            min-width: 16rem;
        }

        /* El estado colapsado se aplica al contenedor; el botón permanece accesible. */
        #app-shell #sidebar {
            flex: 0 0 16rem;
            width: 16rem;
            min-width: 16rem;
            transition: flex-basis .25s ease, width .25s ease, min-width .25s ease, opacity .2s ease;
        }

        #app-shell.is-sidebar-collapsed #sidebar {
            flex: 0 0 0;
            width: 0;
            min-width: 0;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            border-right-width: 0;
        }

        #sidebar.is-mobile-open {
            transform: translateX(0);
        }

        /* Base oscura de elementos estructurales; el tema claro los sobrescribe. */
        #sidebar {
            background: #0f172a;
            border-color: #1e293b;
        }

        #sidebar .border-b,
        #sidebar .border-t {
            border-color: #1e293b;
        }

        #sidebar .text-slate-900,
        #sidebar .text-slate-700 {
            color: #f1f5f9;
        }

        #sidebar .text-slate-600,
        #sidebar .text-slate-500 {
            color: #cbd5e1;
        }

        #sidebar .hover\:bg-slate-100:hover {
            background: #1e293b;
        }

        #sidebar .bg-slate-100 {
            background: #1e293b;
        }

        main>header {
            background: rgba(15, 23, 42, .92);
            border-color: #1e293b;
        }

        main>header .bg-white {
            background: #1e293b;
        }

        main>header .text-slate-600 {
            color: #cbd5e1;
        }

        main>div {
            background: #020617;
        }

        /* Cuando se selecciona el tema claro, se recuperan superficies claras. */
        html:not(.dark),
        html:not(.dark) body,
        html:not(.dark) #app-shell {
            background: #f8fafc;
            color: #0f172a;
        }

        html:not(.dark) #sidebar {
            background: #fff;
            border-color: #e2e8f0;
        }

        html:not(.dark) #sidebar .border-b,
        html:not(.dark) #sidebar .border-t {
            border-color: #e2e8f0;
        }

        html:not(.dark) #sidebar .text-slate-900,
        html:not(.dark) #sidebar .text-slate-700 {
            color: #0f172a;
        }

        html:not(.dark) #sidebar .text-slate-600,
        html:not(.dark) #sidebar .text-slate-500 {
            color: #475569;
        }

        html:not(.dark) #sidebar .bg-slate-100 {
            background: #f1f5f9;
        }

        html:not(.dark) main>header {
            background: rgba(255, 255, 255, .92);
            border-color: #e2e8f0;
        }

        html:not(.dark) main>header .bg-slate-800 {
            background: #fff;
        }

        html:not(.dark) main>header .text-slate-300 {
            color: #475569;
        }

        html:not(.dark) main>div {
            background: #f8fafc;
        }

        .nav-link {
            display: block;
            border-radius: .65rem;
            padding: .55rem .75rem;
            color: #94a3b8;
            font-size: .8125rem;
            font-weight: 500;
            transition: background-color .15s, color .15s;
        }

        .nav-link:hover {
            background: rgba(51, 65, 85, .55);
            color: #f8fafc;
        }

        .nav-link-active {
            background: rgba(14, 165, 233, .10);
            color: #7dd3fc;
            font-weight: 650;
        }

        .nav-group[open]>summary {
            color: #7dd3fc;
        }

        html:not(.dark) .nav-link {
            color: #64748b;
        }

        html:not(.dark) .nav-link:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        html:not(.dark) .nav-link-active {
            background: #eff6ff;
            color: #0369a1;
        }

        html:not(.dark) .nav-group[open]>summary {
            color: #0284c7;
        }

        @media (max-width: 1023px) {

            #app-shell #sidebar,
            #app-shell.is-sidebar-collapsed #sidebar {
                flex: 0 0 16rem;
                width: 16rem;
                min-width: 16rem;
                opacity: 1;
                pointer-events: auto;
            }
        }
    </style>

    <script>
        (() => {
            const root = document.documentElement;
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const shell = document.getElementById('app-shell');
            const toggle = document.getElementById('sidebar-toggle');
            const themeToggle = document.getElementById('theme-toggle');
            const sun = document.getElementById('theme-sun');
            const moon = document.getElementById('theme-moon');
            const isMobile = () => window.matchMedia('(max-width: 1023px)').matches;

            const setExpanded = (expanded) => toggle?.setAttribute('aria-expanded', String(expanded));
            const closeMobileSidebar = () => {
                sidebar?.classList.remove('is-mobile-open');
                sidebar?.classList.add('-translate-x-full');
                overlay?.classList.add('hidden');
                setExpanded(false);
            };

            toggle?.addEventListener('click', () => {
                if (isMobile()) {
                    const opening = !sidebar.classList.contains('is-mobile-open');
                    sidebar.classList.toggle('is-mobile-open', opening);
                    sidebar.classList.toggle('-translate-x-full', !opening);
                    overlay?.classList.toggle('hidden', !opening);
                    setExpanded(opening);
                    return;
                }

                const collapsed = shell.classList.toggle('is-sidebar-collapsed');
                setExpanded(!collapsed);
            });

            overlay?.addEventListener('click', closeMobileSidebar);
            document.querySelectorAll('[data-sidebar-close]').forEach((element) => {
                element.addEventListener('click', closeMobileSidebar);
            });

            const updateThemeButton = () => {
                const dark = root.classList.contains('dark');
                sun?.classList.toggle('hidden', !dark);
                moon?.classList.toggle('hidden', dark);
                themeToggle?.setAttribute('aria-pressed', String(dark));
                themeToggle?.setAttribute('title', dark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro');
            };

            themeToggle?.addEventListener('click', () => {
                const dark = root.classList.toggle('dark');
                try {
                    localStorage.setItem('theme', dark ? 'dark' : 'light');
                } catch (error) {
                    /* El tema seguirá funcionando durante esta sesión. */ }
                updateThemeButton();
            });

            window.addEventListener('resize', () => {
                if (isMobile()) {
                    shell?.classList.remove('is-sidebar-collapsed');
                    closeMobileSidebar();
                } else {
                    sidebar?.classList.remove('is-mobile-open', '-translate-x-full');
                    overlay?.classList.add('hidden');
                    setExpanded(!shell?.classList.contains('is-sidebar-collapsed'));
                }
            });

            // En escritorio se muestra abierto; en móvil permanece cerrado hasta pulsar el botón.
            if (isMobile()) closeMobileSidebar();
            else {
                shell?.classList.remove('is-sidebar-collapsed');
                setExpanded(true);
            }
            updateThemeButton();
        })();
    </script>
    @stack('scripts')
</body>

</html>