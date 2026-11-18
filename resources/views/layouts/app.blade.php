<!DOCTYPE html>
<html lang="es" class="h-full" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | EwiflorWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Aplica el tema antes de pintar, evita flash --}}
    <script>
        (() => {
            let theme = 'dark';
            try {
                theme = localStorage.getItem('theme') || 'dark';
            } catch (e) {}
            document.documentElement.classList.toggle('dark', theme !== 'light');
        })();
    </script>

    <style>
        :root {
            --primary: #0B6E45;
            --primary-hover: #095638;
        }

        /* ── Sidebar: comportamiento estructural ───────────────── */
        #app-shell #sidebar {
            flex: 0 0 16rem;
            width: 16rem;
            min-width: 16rem;
            transition: flex-basis .28s ease, width .28s ease, min-width .28s ease, opacity .2s ease;
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

        /* ── Animaciones ──────────────────────────────────────── */
        @keyframes fadeInSoft {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim-fade-in {
            animation: fadeInSoft .35s cubic-bezier(.16, 1, .3, 1) both;
        }

        @media (prefers-reduced-motion: reduce) {

            .anim-fade-in,
            #app-shell #sidebar {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>

<body class="h-full overflow-hidden bg-slate-50 text-slate-900 antialiased transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100">
    @php
    $usuarioActual = auth()->user();
    $rolActual = $usuarioActual?->nombre_rol;
    $puedeVender = in_array($rolActual, ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'], true);
    $puedeGestionarUsuarios = $usuarioActual?->puedeGestionarUsuarios() ?? false;
    $fotoPerfil = $usuarioActual?->foto_perfil;
    $nombreAvatar = trim((string) ($usuarioActual?->nombre_usuario ?? 'U'));
    $inicialAvatar = strtoupper(substr($nombreAvatar, 0, 1));
    @endphp

    <div id="app-shell" class="relative flex h-screen overflow-hidden bg-slate-50 dark:bg-slate-950">
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden" data-sidebar-close></div>

        {{-- ─────────────────── Sidebar ─────────────────── --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-all duration-300 ease-in-out dark:border-slate-800 dark:bg-slate-900 lg:relative lg:translate-x-0 lg:shadow-sm">

            {{-- Marca --}}
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 px-5 dark:border-slate-800">
                <a href="{{ route('dashboard') }}" class="group flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#0B6E45] text-white shadow-lg shadow-[#0B6E45]/20 transition-transform duration-300 group-hover:scale-105">
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
                <button type="button" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white lg:hidden" data-sidebar-close aria-label="Cerrar menú">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Navegación principal">
                <div>
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">Principal</p>
                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-[#0B6E45] dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
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

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR'], true))
                    <details class="nav-group group mt-1" {{ request()->routeIs('promociones.*') ? 'open' : '' }}>
                        <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80 [&::-webkit-details-marker]:hidden">
                            <svg class="h-5 w-5 shrink-0 text-fuchsia-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.8.9.9a3.5 3.5 0 0 0 2.1.7 2.6 2.6 0 0 0 2.6-2.6 2.4 2.4 0 0 0-1.2-2.2l-2.9-1.4a2.4 2.4 0 0 1-1.2-2.2A2.6 2.6 0 0 1 11.9 6a3.5 3.5 0 0 1 2.1.7l.9.9" />
                            </svg>
                            <span class="flex-1">Promociones</span>
                            <svg class="h-4 w-4 text-slate-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="ml-5 mt-1 space-y-1 border-l border-slate-200 pl-4 dark:border-slate-700">
                            <a href="{{ route('promociones.index') }}" class="nav-link {{ request()->routeIs('promociones.index', 'promociones.create', 'promociones.edit', 'promociones.update', 'promociones.toggle', 'promociones.destroy') ? 'nav-link-active' : '' }}">Descuentos</a>
                            <a href="{{ route('promociones.combos.index') }}" class="nav-link {{ request()->routeIs('promociones.combos.*') ? 'nav-link-active' : '' }}">Combos</a>
                        </div>
                    </details>
                    @endif

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
                    <a href="{{ route('usuarios.index') }}" class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('usuarios.*') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6" />
                        </svg>
                        Usuarios
                    </a>
                    @endif

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('sucursales.index') }}" class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('sucursales.*') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                        </svg>
                        Sucursales
                    </a>
                    @endif

                    @if ($rolActual === 'GERENTE')
                    <a href="{{ route('auditoria.index') }}" class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('auditoria.*') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Auditoría
                    </a>
                    @endif

                    @if (in_array($rolActual, ['SERVICIO_TECNICO'], true))
                    <a href="{{ route('serviciotecnico.bandeja') }}" class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('serviciotecnico.bandeja*') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Bandeja servicio técnico
                    </a>
                    @endif

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('serviciotecnico.gestion') }}" class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('serviciotecnico.gestion*') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20h7m-10 0v3m0 0v3m0-3h3m-3 0h-3" />
                        </svg>
                        Gestión servicio técnico
                    </a>
                    @endif

                    @if (in_array($rolActual, ['VENDEDOR', 'GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('serviciotecnico.nueva') }}" class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('serviciotecnico.nueva*') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Nueva solicitud servicio técnico
                    </a>
                    @endif

                    @if (in_array($rolActual, ['SERVICIO_TECNICO', 'GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('serviciotecnico.garantias.por_vencer') }}" class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('serviciotecnico.garantias.por_vencer*') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Garantías por vencer
                    </a>
                    @endif

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'], true))
                    <a href="{{ route('precios.tipo-cambio.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('precios.tipo-cambio.*') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h14m0 0-3-3m3 3-3 3M17 17H3m0 0 3 3m-3-3 3-3" />
                        </svg>
                        Tipo de cambio
                    </a>
                    @endif

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('comisiones.index') }}" class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('comisiones.*') ? 'bg-emerald-50 text-[#0B6E45] ring-1 ring-inset ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/80 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Comisiones
                    </a>
                    @endif
                </div>
                @endif
            </nav>

            <div class="shrink-0 border-t border-slate-200 p-3 dark:border-slate-800">
                <a href="/" class="mb-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                    <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                    </svg>
                    Página principal
                </a>

                <div class="flex items-center gap-3 rounded-xl bg-slate-100 p-2.5 dark:bg-slate-800/70">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-emerald-50 text-sm font-bold text-[#0B6E45] ring-1 ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25">
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
                        <button type="submit" class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-500/10 dark:hover:text-red-300" title="Cerrar sesión" aria-label="Cerrar sesión">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ─────────────────── Main ─────────────────── --}}
        <main class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <header class="z-20 flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white/85 px-4 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/75 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button id="sidebar-toggle" type="button"
                        class="rounded-xl border border-slate-200 bg-white p-2 text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900 active:scale-95 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                        aria-label="Alternar menú" aria-controls="sidebar" aria-expanded="true">
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
                    <button id="theme-toggle" type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900 active:scale-95 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                        title="Cambiar tema" aria-label="Cambiar tema">
                        <svg id="theme-sun" class="hidden h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
                        </svg>
                        <svg id="theme-moon" class="h-4 w-4 text-[#0B6E45] dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5 8.5 8.5 0 1 0 20.5 15.5Z" />
                        </svg>
                        <span class="hidden sm:inline">Tema</span>
                    </button>
                    <a href="{{route('catalogo.productos.index')}}">
                        <div class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-emerald-50 text-sm font-bold text-[#0B6E45] ring-1 ring-emerald-200 dark:bg-[#0B6E45]/20 dark:text-emerald-300 dark:ring-emerald-500/25" title="{{ $usuarioActual?->nombre_usuario }}">
                            @if ($fotoPerfil)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($fotoPerfil) }}" alt="Foto de perfil" class="h-full w-full object-cover">
                            @else
                            {{ $inicialAvatar }}
                            @endif
                        </div>
                    </a>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto bg-slate-50 px-4 py-5 dark:bg-slate-950 sm:px-6 sm:py-6">
                <div class="anim-fade-in mx-auto w-full max-w-7xl">
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
        /* Nav links (solo estructura + hover; colores vía Tailwind) */
        .nav-link {
            display: block;
            border-radius: .65rem;
            padding: .55rem .75rem;
            font-size: .8125rem;
            font-weight: 500;
            color: #64748b;
            transition: background-color .15s ease, color .15s ease;
        }

        .nav-link:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .nav-link-active {
            background: #ecfdf5;
            color: #0B6E45;
            font-weight: 650;
        }

        .dark .nav-link {
            color: #94a3b8;
        }

        .dark .nav-link:hover {
            background: rgba(51, 65, 85, .55);
            color: #f8fafc;
        }

        .dark .nav-link-active {
            background: rgba(11, 110, 69, .20);
            color: #6ee7b7;
        }

        .nav-group[open]>summary {
            color: #0B6E45;
        }

        .dark .nav-group[open]>summary {
            color: #6ee7b7;
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
            document.querySelectorAll('[data-sidebar-close]').forEach((el) => {
                el.addEventListener('click', closeMobileSidebar);
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
                } catch (e) {}
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