<header class="navbar relative z-50" id="site-navbar">

    <div class="container-main navbar-inner !grid !grid-cols-[auto_1fr_auto] !items-center !gap-3">


        {{-- ISLA IZQUIERDA: LOGO --}}
        <div class="brand-island">

            <a href="{{ route('catalogo.publico') }}" class="brand">

                <span class="brand-icon">
                    <img
                        src="{{ asset('images/ewiflor-logo.webp') }}"
                        alt="Logo EWIFLOR">
                </span>

                <span>
                    <span class="brand-name">EWIFLOR</span>
                    <span class="brand-subtitle">Importaciones</span>
                </span>

            </a>

        </div>



        {{-- ISLA CENTRAL: NAVEGACIÓN --}}
        <nav class="nav-island !mx-auto" aria-label="Navegación principal">

            {{-- Enlaces escritorio --}}
            <div class="nav-links hidden items-center justify-center gap-2 md:flex">

                <a href="{{ route('catalogo.publico') }}"
                    class="nav-link {{ $esInicio ? 'active' : '' }}">
                    Inicio
                </a>

                <a href="{{ $base }}#categorias" class="nav-link">
                    Categorías
                </a>

                <a href="{{ route('publico.catalogo') }}"
                    class="nav-link {{ $enCatalogo ? 'active' : '' }}"
                    @if($enCatalogo) aria-current="page" @endif>
                    Catálogo
                </a>

                <a href="{{ route('publico.servicio.tecnico') }}"
                    class="nav-link {{ $enServicio ? 'active' : '' }}"
                    @if($enServicio) aria-current="page" @endif>
                    Servicio técnico
                </a>

                <a href="{{ route('publico.nosotros') }}"
                    class="nav-link {{ $enNosotros ? 'active' : '' }}"
                    @if($enNosotros) aria-current="page" @endif>
                    Nosotros
                </a>

            </div>


            {{-- Menú móvil --}}
            <details class="mobile-navigation md:hidden">

                <summary class="mobile-menu-button" aria-label="Abrir menú">
                    <span class="material-symbols-rounded" aria-hidden="true">
                        menu
                    </span>
                </summary>

                <div class="mobile-menu-dropdown">

                    <a href="{{ route('catalogo.publico') }}"
                        class="{{ $esInicio ? 'active' : '' }}">
                        Inicio
                    </a>

                    <a href="{{ $base }}#categorias">
                        Categorías
                    </a>

                    <a href="{{ route('publico.catalogo') }}"
                        class="{{ $enCatalogo ? 'active' : '' }}">
                        Catálogo
                    </a>

                    <a href="{{ route('publico.servicio.tecnico') }}"
                        class="{{ $enServicio ? 'active' : '' }}">
                        Servicio técnico
                    </a>

                    <a href="{{ route('publico.nosotros') }}"
                        class="{{ $enNosotros ? 'active' : '' }}">
                        Nosotros
                    </a>

                    <div class="mobile-menu-divider"></div>

                    <a href="{{ route('login') }}" class="mobile-login-link">
                        Acceso empleados
                    </a>

                </div>

            </details>

        </nav>


        {{-- ISLA DERECHA: ACCIONES --}}
        <div class="login-island flex items-center justify-center gap-2">

            <button
                type="button"
                id="theme-toggle"
                class="theme-button"
                aria-label="Cambiar tema"
                title="Cambiar tema">
                <span id="theme-icon" class="material-symbols-rounded">
                    dark_mode
                </span>
            </button>

            <a href="{{ route('login') }}"
                class="employee-link desktop-employee-link">
                Acceso empleados
            </a>

        </div>

    </div>


    {{-- ESTILOS DEL MENÚ MÓVIL --}}
    <style>
        .mobile-navigation {
            position: relative;
            flex-shrink: 0;
        }

        .mobile-navigation>summary {
            display: flex;
            align-items: center;
            justify-content: center;
            list-style: none;
            cursor: pointer;
        }

        .mobile-navigation>summary::-webkit-details-marker {
            display: none;
        }

        .mobile-menu-dropdown {
            position: fixed;
            top: auto;
            right: auto;
            left: 50%;
            transform: translateX(-50%);

            z-index: 9999;

            display: none;
            flex-direction: column;
            gap: 4px;

            width: calc(100vw - 32px);
            max-width: 360px;
            max-height: 70vh;
            overflow-y: auto;

            margin-top: 12px;
            padding: 12px;

            background: var(--color-surface, #ffffff);
            border: 1px solid var(--color-line, #e5e7eb);
            border-radius: 16px;

            box-shadow: 0 12px 35px rgb(0 0 0 / 12%);
        }

        .mobile-navigation[open] .mobile-menu-dropdown {
            display: flex;
        }

        .mobile-menu-dropdown a {
            display: block;
            width: 100%;
            padding: 12px 14px;

            border-radius: 10px;

            color: var(--color-ink, #1f2937);
            font-size: 14px;
            font-weight: 500;
            text-align: left;
            text-decoration: none;

            transition: background-color 180ms ease;
        }

        .mobile-menu-dropdown a:hover,
        .mobile-menu-dropdown a.active {
            background: var(--color-surface-2, #f1f5f9);
            color: var(--color-brand-green, #2A9832);
        }

        .mobile-menu-divider {
            height: 1px;
            margin: 5px 0;
            background: var(--color-line, #e5e7eb);
        }

        .mobile-menu-dropdown .mobile-login-link {
            color: var(--color-brand-green, #2A9832);
            font-weight: 700;
        }

        @media (min-width: 768px) {
            .mobile-navigation {
                display: none !important;
            }
        }

        @media (max-width: 767px) {
            .navbar-inner {
                grid-template-columns: auto 1fr auto !important;
            }

            .brand-island .brand-subtitle {
                display: none;
            }

            .brand-island .brand-name {
                font-size: 13px;
            }

            .login-island .desktop-employee-link {
                display: none !important;
            }

            .nav-island {
                justify-self: end;
            }
        }

        /* =========================================
   HEADER: TRES ISLAS INDEPENDIENTES
========================================= */

        .navbar-inner {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            justify-content: initial;
            gap: 16px;
        }

        /* Isla izquierda: identidad de EWIFLOR */
        .brand-island {
            position: relative;
            justify-self: start;
            min-width: 0;
            display: flex;
            align-items: center;

            padding: 7px 16px;
            border: 1px solid var(--border);
            border-radius: 999px;

            background: var(--nav-bg);
            box-shadow: var(--shadow);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            pointer-events: auto;
        }

        .brand-island .brand {
            gap: 12px;
        }

        .brand-island .brand-icon {
            width: 54px;
            height: 54px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
            background: transparent;
        }

        .brand-island .brand-icon img {
            display: block;
            width: 100%;
            height: 100%;
            max-width: none;
            object-fit: contain;
        }

        /* Isla central: navegación */
        .nav-island {
            position: relative;
            justify-self: center;
            margin: 0;
            max-width: 100%;
            min-width: 0;
        }

        /* Isla derecha: acciones */
        .login-island {
            position: relative;
            right: auto;
            justify-self: end;

            display: flex;
            align-items: center;
            gap: 5px;

            pointer-events: auto;
        }

        /* =========================================
   RESPONSIVE
========================================= */

        @media (max-width: 1050px) and (min-width: 761px) {
            .navbar-inner {
                gap: 8px;
            }

            .brand-island {
                padding-inline: 10px;
            }

            .brand-island .brand {
                gap: 7px;
            }

            .brand-island .brand-icon {
                width: 44px;
                height: 44px;
            }

            .nav-island {
                gap: 6px;
                padding-inline: 8px;
            }

            .nav-link {
                padding-inline: 7px;
                font-size: .68rem;
            }

            .employee-link {
                padding-inline: 10px;
                font-size: .68rem;
            }
        }

        @media (max-width: 760px) {
            .navbar-inner {
                width: calc(100% - 24px);
                grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
                gap: 6px;
            }

            .brand-island {
                justify-self: start;
                padding: 5px 9px;
            }

            .brand-island .brand-icon {
                width: 38px;
                height: 38px;
            }

            .brand-island .brand {
                gap: 7px;
            }

            .brand-island .brand-name {
                font-size: .82rem;
            }

            .brand-island .brand-subtitle {
                font-size: .48rem;
            }

            .nav-island {
                justify-self: center;
                width: auto;
                max-width: none;
                padding: 4px;
            }

            .login-island {
                position: relative;
                justify-self: end;
                padding: 3px;
            }

            .login-island .desktop-employee-link {
                display: none !important;
            }
        }

        @media (max-width: 380px) {
            .navbar-inner {
                width: calc(100% - 16px);
                gap: 4px;
            }

            .brand-island {
                padding-inline: 6px;
            }

            .brand-island .brand-icon {
                width: 32px;
                height: 32px;
            }

            .brand-island .brand-name {
                font-size: .74rem;
            }

            .brand-island .brand-subtitle {
                font-size: .43rem;
            }
        }
    </style>

</header>