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
            document.documentElement.setAttribute('data-theme', theme === 'light' ? 'light' : 'dark');
        })();
    </script>

    <style>
        :root {
            --primary: #2A9832;
            --primary-hover: #23822B;
        }

        /* Fondo con glow sutil (paleta de marca) */
        body {
            background-color: var(--bg);
            background-image:
                radial-gradient(1200px 600px at 10% -10%, rgba(42, 152, 50, .16), transparent 60%),
                radial-gradient(1000px 500px at 90% 0%, rgba(130, 183, 35, .10), transparent 55%),
                radial-gradient(900px 700px at 50% 120%, rgba(22, 89, 143, .14), transparent 60%);
            background-attachment: fixed;
        }

        /* ── Sidebar: comportamiento estructural ───────────────── */
        #app-shell #sidebar {
            flex: 0 0 16rem;
            width: 16rem;
            min-width: 16rem;
            transition: flex-basis .28s ease, width .28s ease, min-width .28s ease;
        }

        /* Estado por defecto: RAIL de iconos */
        #app-shell.is-sidebar-collapsed #sidebar {
            flex: 0 0 4.75rem;
            width: 4.75rem;
            min-width: 4.75rem;
        }

        /* Ocultar textos/chevrones/submenús en estado rail */
        #app-shell.is-sidebar-collapsed #sidebar .nav-label,
        #app-shell.is-sidebar-collapsed #sidebar .nav-section-title,
        #app-shell.is-sidebar-collapsed #sidebar .nav-chevron,
        #app-shell.is-sidebar-collapsed #sidebar .nav-submenu,
        #app-shell.is-sidebar-collapsed #sidebar .brand-text,
        #app-shell.is-sidebar-collapsed #sidebar .user-info,
        #app-shell.is-sidebar-collapsed #sidebar .footer-link-text {
            display: none !important;
        }

        /* Centrar iconos en estado rail */
        #app-shell.is-sidebar-collapsed #sidebar .nav-item,
        #app-shell.is-sidebar-collapsed #sidebar .nav-group>summary,
        #app-shell.is-sidebar-collapsed #sidebar .footer-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        #app-shell.is-sidebar-collapsed #sidebar .brand-link {
            justify-content: center;
        }

        #app-shell.is-sidebar-collapsed #sidebar .user-card {
            flex-direction: column;
            gap: .35rem;
            padding: .5rem;
        }

        /* ── Móvil: rail persistente + overlay al expandir ─────── */
        @media (max-width: 1023px) {
            #app-shell #sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 40;
            }

            /* Reserva el espacio del rail en el layout flex */
            #app-shell::before {
                content: '';
                flex: 0 0 4.75rem;
                width: 4.75rem;
            }

            #app-shell:not(.is-sidebar-collapsed) #sidebar {
                box-shadow: var(--shadow-modal);
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

        /* ── Nav links (paleta dinámica) ──────────────────────── */
        .nav-link {
            display: block;
            border-radius: .65rem;
            padding: .55rem .75rem;
            font-size: .8125rem;
            font-weight: 500;
            color: var(--muted);
            transition: background-color .15s ease, color .15s ease;
        }

        .nav-link:hover {
            background: var(--surface-2);
            color: var(--text);
        }

        .nav-link-active {
            background: color-mix(in srgb, var(--color-brand-green) 14%, transparent);
            color: var(--color-brand-green);
            font-weight: 650;
        }

        [data-theme="dark"] .nav-link-active {
            background: rgba(42, 152, 50, .20);
            color: #86efac;
        }

        .nav-group[open]>summary {
            color: var(--color-brand-green);
        }

        [data-theme="dark"] .nav-group[open]>summary {
            color: #86efac;
        }

        /* ── Skeleton Loading ─────────────────────────────────── */
        @keyframes skeletonShimmer {
            0% {
                background-position: -400px 0;
            }

            100% {
                background-position: 400px 0;
            }
        }

        .skeleton {
            background: linear-gradient(90deg,
                    rgba(148, 163, 184, .10) 25%,
                    rgba(148, 163, 184, .22) 37%,
                    rgba(148, 163, 184, .10) 63%);
            background-size: 800px 100%;
            animation: skeletonShimmer 1.4s infinite linear;
            border-radius: .9rem;
        }

        [data-theme="dark"] .skeleton {
            background: linear-gradient(90deg,
                    rgba(255, 255, 255, .04) 25%,
                    rgba(255, 255, 255, .10) 37%,
                    rgba(255, 255, 255, .04) 63%);
            background-size: 800px 100%;
        }
    </style>
</head>

<body class="h-full overflow-hidden bg-canvas text-ink antialiased transition-colors duration-200">
    @php
    $usuarioActual = auth()->user();
    $rolActual = $usuarioActual?->nombre_rol;
    $puedeVender = in_array($rolActual, ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'], true);
    $puedeGestionarUsuarios = $usuarioActual?->puedeGestionarUsuarios() ?? false;
    $fotoPerfil = $usuarioActual?->foto_perfil;
    $nombreAvatar = trim((string) ($usuarioActual?->nombre_usuario ?? 'U'));
    $inicialAvatar = strtoupper(substr($nombreAvatar, 0, 1));
    @endphp

    <div id="app-shell" class="relative flex h-screen overflow-hidden bg-canvas is-sidebar-collapsed">
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden" data-sidebar-close></div>

        {{-- ─────────────────── Sidebar ─────────────────── --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex flex-col border-r border-line bg-surface/95 backdrop-blur-xl transition-all duration-300 ease-in-out lg:relative lg:shadow-soft">

            {{-- Marca --}}
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-line px-5">
                <a href="{{ route('dashboard') }}" class="brand-link group flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-green text-white shadow-soft transition-transform duration-300 group-hover:scale-105">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 7.8 7.5 4.4 7.5-4.4M12 12.2V21" />
                        </svg>
                    </span>
                    <span class="brand-text min-w-0">
                        <span class="block truncate text-base font-bold tracking-tight text-ink">EwiflorWeb</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-muted">Gestión empresarial</span>
                    </span>
                </a>
                <button type="button" class="rounded-lg p-2 text-muted transition hover:bg-surface-2 hover:text-ink lg:hidden" data-sidebar-close aria-label="Cerrar menú">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="Navegación principal">
                <div>
                    <p class="nav-section-title mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-muted">Principal</p>
                    <a href="{{ route('dashboard') }}" title="Dashboard" class="nav-item group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-brand-green' : 'text-muted group-hover:text-ink' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="3" width="8" height="8" rx="2" />
                            <rect x="13" y="3" width="8" height="5" rx="2" />
                            <rect x="13" y="10" width="8" height="11" rx="2" />
                            <rect x="3" y="13" width="8" height="8" rx="2" />
                        </svg>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </div>

                <div>
                    <p class="nav-section-title mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-muted">Operaciones</p>

                    <details class="nav-group group" data-href="{{ route('catalogo.productos.index') }}" {{ request()->routeIs('catalogo.productos.*', 'catalogo.marcas.*') ? 'open' : '' }}>
                        <summary title="Catálogo" class="nav-item flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-muted transition hover:bg-surface-2 hover:text-ink [&::-webkit-details-marker]:hidden">
                            <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 9 4.5v9L12 21l-9-4.5v-9L12 3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3.5 7.7 8.5 4.5 8.5-4.5M12 12.2V21" />
                            </svg>
                            <span class="nav-label flex-1">Catálogo</span>
                            <svg class="nav-chevron h-4 w-4 text-muted transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="nav-submenu ml-5 mt-1 space-y-1 border-l border-line pl-4">
                            <a href="{{ route('catalogo.productos.index') }}" class="nav-link {{ request()->routeIs('catalogo.productos.*') ? 'nav-link-active' : '' }}">Productos</a>
                            <a href="{{ route('catalogo.marcas.index') }}" class="nav-link {{ request()->routeIs('catalogo.marcas.*') ? 'nav-link-active' : '' }}">Marcas</a>
                        </div>
                    </details>

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR'], true))
                    <details class="nav-group group mt-1" data-href="{{ route('promociones.index') }}" {{ request()->routeIs('promociones.*') ? 'open' : '' }}>
                        <summary title="Promociones" class="nav-item flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-muted transition hover:bg-surface-2 hover:text-ink [&::-webkit-details-marker]:hidden">
                            <svg class="h-5 w-5 shrink-0 text-brand-lime" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.8.9.9a3.5 3.5 0 0 0 2.1.7 2.6 2.6 0 0 0 2.6-2.6 2.4 2.4 0 0 0-1.2-2.2l-2.9-1.4a2.4 2.4 0 0 1-1.2-2.2A2.6 2.6 0 0 1 11.9 6a3.5 3.5 0 0 1 2.1.7l.9.9" />
                            </svg>
                            <span class="nav-label flex-1">Promociones</span>
                            <svg class="nav-chevron h-4 w-4 text-muted transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="nav-submenu ml-5 mt-1 space-y-1 border-l border-line pl-4">
                            <a href="{{ route('promociones.index') }}" class="nav-link {{ request()->routeIs('promociones.index', 'promociones.create', 'promociones.edit', 'promociones.update', 'promociones.toggle', 'promociones.destroy') ? 'nav-link-active' : '' }}">Descuentos</a>
                            <a href="{{ route('promociones.combos.index') }}" class="nav-link {{ request()->routeIs('promociones.combos.*') ? 'nav-link-active' : '' }}">Combos</a>
                        </div>
                    </details>
                    @endif

                    <details class="nav-group group mt-1" data-href="{{ route('inventario.existencias.index') }}" {{ request()->routeIs('inventario.existencias.*', 'inventario.movimientos.*') ? 'open' : '' }}>
                        <summary title="Inventario" class="nav-item flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-muted transition hover:bg-surface-2 hover:text-ink [&::-webkit-details-marker]:hidden">
                            <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 7.8 12 12l8.5-4.2M12 12v9" />
                            </svg>
                            <span class="nav-label flex-1">Inventario</span>
                            <svg class="nav-chevron h-4 w-4 text-muted transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="nav-submenu ml-5 mt-1 space-y-1 border-l border-line pl-4">
                            <a href="{{ route('inventario.existencias.index') }}" class="nav-link {{ request()->routeIs('inventario.existencias.*') ? 'nav-link-active' : '' }}">Existencias</a>
                            <a href="{{ route('inventario.movimientos.index') }}" class="nav-link {{ request()->routeIs('inventario.movimientos.*') ? 'nav-link-active' : '' }}">Movimientos</a>
                        </div>
                    </details>

                    @if ($puedeVender)
                    <details class="nav-group group mt-1" data-href="{{ route('clientes.index') }}" {{ request()->routeIs('clientes.*', 'cotizaciones.*', 'ventas.*') ? 'open' : '' }}>
                        <summary title="Comercial" class="nav-item flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-muted transition hover:bg-surface-2 hover:text-ink [&::-webkit-details-marker]:hidden">
                            <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.2 11.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 1.9-1.4L22 8H6" />
                                <circle cx="10" cy="20" r="1" />
                                <circle cx="18" cy="20" r="1" />
                            </svg>
                            <span class="nav-label flex-1">Comercial</span>
                            <svg class="nav-chevron h-4 w-4 text-muted transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>
                        <div class="nav-submenu ml-5 mt-1 space-y-1 border-l border-line pl-4">
                            <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'nav-link-active' : '' }}">Clientes</a>
                            <a href="{{ route('cotizaciones.index') }}" class="nav-link {{ request()->routeIs('cotizaciones.*') ? 'nav-link-active' : '' }}">Cotizaciones</a>
                            <a href="{{ route('ventas.index') }}" class="nav-link {{ request()->routeIs('ventas.*') ? 'nav-link-active' : '' }}">Ventas</a>
                        </div>
                    </details>
                    @endif
                </div>

                @if ($puedeGestionarUsuarios || in_array($rolActual, ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'], true))
                <div>
                    <p class="nav-section-title mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-muted">Administración</p>

                    @if ($puedeGestionarUsuarios)
                    <a href="{{ route('usuarios.index') }}" title="Usuarios" class="nav-item group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('usuarios.*') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6" />
                        </svg>
                        <span class="nav-label">Usuarios</span>
                    </a>
                    @endif

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('sucursales.index') }}" title="Sucursales" class="nav-item group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('sucursales.*') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                        </svg>
                        <span class="nav-label">Sucursales</span>
                    </a>
                    @endif

                    @if ($rolActual === 'GERENTE')
                    <a href="{{ route('auditoria.index') }}" title="Auditoría" class="nav-item group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('auditoria.*') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="nav-label">Auditoría</span>
                    </a>
                    @endif

                    @if (in_array($rolActual, ['SERVICIO_TECNICO'], true))
                    <a href="{{ route('serviciotecnico.bandeja') }}" title="Bandeja servicio técnico" class="nav-item group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('serviciotecnico.bandeja*') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="nav-label">Bandeja servicio técnico</span>
                    </a>
                    @endif

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('serviciotecnico.gestion') }}" title="Gestión servicio técnico" class="nav-item group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('serviciotecnico.gestion*') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20h7m-10 0v3m0 0v3m0-3h3m-3 0h-3" />
                        </svg>
                        <span class="nav-label">Gestión servicio técnico</span>
                    </a>
                    @endif

                    @if (in_array($rolActual, ['VENDEDOR', 'GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('serviciotecnico.nueva') }}" title="Nueva solicitud servicio técnico" class="nav-item group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('serviciotecnico.nueva*') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="nav-label">Nueva solicitud servicio técnico</span>
                    </a>
                    @endif

                    @if (in_array($rolActual, ['SERVICIO_TECNICO', 'GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('serviciotecnico.garantias.por_vencer') }}" title="Garantías por vencer" class="nav-item group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('serviciotecnico.garantias.por_vencer*') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="nav-label">Garantías por vencer</span>
                    </a>
                    @endif

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR', 'VENDEDOR'], true))
                    <a href="{{ route('precios.tipo-cambio.index') }}" title="Tipo de cambio" class="nav-item group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('precios.tipo-cambio.*') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h14m0 0-3-3m3 3-3 3M17 17H3m0 0 3 3m-3-3 3-3" />
                        </svg>
                        <span class="nav-label">Tipo de cambio</span>
                    </a>
                    @endif

                    @if (in_array($rolActual, ['GERENTE', 'ADMINISTRADOR'], true))
                    <a href="{{ route('comisiones.index') }}" title="Comisiones" class="nav-item group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('comisiones.*') ? 'bg-brand-green/15 text-brand-green ring-1 ring-inset ring-brand-green/25' : 'text-muted hover:bg-surface-2 hover:text-ink' }}">
                        <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="nav-label">Comisiones</span>
                    </a>
                    @endif
                </div>
                @endif
            </nav>

            <div class="shrink-0 border-t border-line p-3">
                <a href="/" title="Página principal" class="footer-link mb-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface-2 hover:text-ink">
                    <svg class="h-5 w-5 shrink-0 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                    </svg>
                    <span class="footer-link-text">Página principal</span>
                </a>

                <div class="user-card flex items-center gap-3 rounded-xl bg-surface-2/70 p-2.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-green/15 text-sm font-bold text-brand-green ring-1 ring-brand-green/25">
                        @if ($fotoPerfil)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($fotoPerfil) }}" alt="Foto de perfil" class="h-full w-full object-cover">
                        @else
                        {{ $inicialAvatar }}
                        @endif
                    </div>
                    <div class="user-info min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-ink">{{ $usuarioActual?->nombre_usuario ?? 'Usuario' }}</p>
                        <p class="truncate text-xs text-muted">{{ $rolActual ?? 'Sin rol' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg p-2 text-muted transition-colors hover:bg-red-500/10 hover:text-red-400" title="Cerrar sesión" aria-label="Cerrar sesión">
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
            <header class="z-20 flex h-16 shrink-0 items-center justify-between border-b border-line bg-nav-bg px-4 backdrop-blur-xl sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button id="sidebar-toggle" type="button"
                        class="rounded-xl border border-line bg-surface-2/60 p-2 text-muted transition-all duration-200 hover:bg-surface-2 hover:text-ink active:scale-95"
                        aria-label="Alternar menú" aria-controls="sidebar" aria-expanded="false">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h1 class="truncate text-base font-semibold tracking-tight text-ink">@yield('title', 'Dashboard')</h1>
                        @hasSection('subtitle')
                        <p class="hidden truncate text-xs text-muted sm:block">@yield('subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Barra de búsqueda (oculta de momento) --}}
                    <div class="relative hidden">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m20 20-3.5-3.5" />
                        </svg>
                        <input type="search" placeholder="Search"
                            class="w-56 rounded-xl border border-line bg-surface-2/60 py-2 pl-9 pr-3 text-sm text-ink placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-green/30" />
                    </div>

                    <button id="theme-toggle" type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-line bg-surface-2/60 px-3 py-2 text-xs font-semibold text-muted transition-all duration-200 hover:bg-surface-2 hover:text-ink active:scale-95"
                        title="Cambiar tema" aria-label="Cambiar tema">
                        <svg id="theme-sun" class="hidden h-4 w-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
                        </svg>
                        <svg id="theme-moon" class="h-4 w-4 text-brand-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5 8.5 8.5 0 1 0 20.5 15.5Z" />
                        </svg>
                        <span class="hidden sm:inline">Tema</span>
                    </button>
                    {{-- Redirección actual conservada --}}
                    <a href="{{ route('catalogo.productos.index') }}">
                        <div class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-brand-green/15 text-sm font-bold text-brand-green ring-1 ring-brand-green/25" title="{{ $usuarioActual?->nombre_usuario }}">
                            @if ($fotoPerfil)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($fotoPerfil) }}" alt="Foto de perfil" class="h-full w-full object-cover">
                            @else
                            {{ $inicialAvatar }}
                            @endif
                        </div>
                    </a>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto bg-canvas px-4 py-5 sm:px-6 sm:py-6">
                <div class="anim-fade-in relative mx-auto w-full max-w-7xl">
                    {{-- Skeleton loading --}}
                    <div id="page-skeleton" class="absolute inset-0 z-10 space-y-4 bg-canvas p-2 transition-opacity duration-500 sm:p-4">
                        <div class="skeleton h-9 w-1/3"></div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="skeleton h-40"></div>
                            <div class="skeleton h-40"></div>
                            <div class="skeleton h-40"></div>
                        </div>
                        <div class="skeleton h-64"></div>
                    </div>

                    @if (session('success'))
                    <div class="mb-5 flex items-start gap-3 rounded-xl border border-brand-green/30 bg-brand-green/10 px-4 py-3 text-sm text-brand-green" role="status">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400" role="alert">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

            /* Colapsa a rail y oculta overlay */
            const collapseSidebar = () => {
                shell?.classList.add('is-sidebar-collapsed');
                overlay?.classList.add('hidden');
                setExpanded(false);
            };

            /* Expande y, en móvil, muestra overlay */
            const expandSidebar = () => {
                shell?.classList.remove('is-sidebar-collapsed');
                if (isMobile()) overlay?.classList.remove('hidden');
                else overlay?.classList.add('hidden');
                setExpanded(true);
            };

            toggle?.addEventListener('click', () => {
                const collapsed = shell.classList.contains('is-sidebar-collapsed');
                if (collapsed) expandSidebar();
                else collapseSidebar();
            });

            /* Click en backdrop cierra (vuelve a rail) */
            overlay?.addEventListener('click', collapseSidebar);
            document.querySelectorAll('[data-sidebar-close]').forEach((el) => {
                el.addEventListener('click', collapseSidebar);
            });

            /* En estado rail, los <summary> navegan directo a su ruta principal */
            document.querySelectorAll('#sidebar details.nav-group').forEach((group) => {
                const summary = group.querySelector('summary');
                summary?.addEventListener('click', (e) => {
                    if (shell.classList.contains('is-sidebar-collapsed')) {
                        e.preventDefault();
                        const href = group.dataset.href;
                        if (href) window.location.href = href;
                    }
                });
            });

            /* Tema */
            const updateThemeButton = () => {
                const dark = root.getAttribute('data-theme') === 'dark';
                sun?.classList.toggle('hidden', !dark);
                moon?.classList.toggle('hidden', dark);
                themeToggle?.setAttribute('aria-pressed', String(dark));
                themeToggle?.setAttribute('title', dark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro');
            };

            themeToggle?.addEventListener('click', () => {
                const isDark = root.getAttribute('data-theme') === 'dark';
                const next = isDark ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                try {
                    localStorage.setItem('theme', next);
                } catch (e) {}
                updateThemeButton();
            });

            /* Responsive: reajusta overlay si cambia de tamaño */
            window.addEventListener('resize', () => {
                const collapsed = shell.classList.contains('is-sidebar-collapsed');
                if (isMobile()) {
                    overlay?.classList.toggle('hidden', collapsed);
                } else {
                    overlay?.classList.add('hidden');
                }
                setExpanded(!collapsed);
            });

            /* Estado inicial: SIEMPRE rail colapsado (escritorio y móvil) */
            collapseSidebar();
            updateThemeButton();

            /* Skeleton loading */
            const hideSkeleton = () => {
                const sk = document.getElementById('page-skeleton');
                if (!sk) return;
                sk.classList.add('opacity-0');
                setTimeout(() => sk.remove(), 500);
            };

            if (document.readyState === 'complete') hideSkeleton();
            else window.addEventListener('load', hideSkeleton);
        })();
    </script>
    @stack('scripts')
</body>

</html>