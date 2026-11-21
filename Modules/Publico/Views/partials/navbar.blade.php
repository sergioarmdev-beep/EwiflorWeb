{{--
    Cabecera pública en dos niveles:
      1. Barra superior (3 islas): identidad · búsqueda de productos · cotizar por WhatsApp.
      2. Barra de navegación convencional (sticky): Inicio, Categorías, Catálogo, Servicio, Nosotros.
--}}

<header class="site-topbar">
    <div class="container-main site-topbar-inner">

        {{-- Isla izquierda: identidad --}}
        <a class="site-brand" href="{{ route('catalogo.publico') }}" aria-label="Ewiflor Importaciones, ir al inicio">
            <img
                src="{{ asset('images/ewiflor-logo.webp') }}"
                alt="Logo Ewiflor"
                class="site-brand-logo"
                width="60"
                height="60"
                fetchpriority="high">
            <span class="site-brand-text">
                <span class="site-brand-name">EWIFLOR</span>
                <span class="site-brand-sub">Importaciones</span>
            </span>
        </a>

        {{-- Isla central: búsqueda en el catálogo existente --}}
        <form class="site-search"
            action="{{ route('publico.catalogo') }}"
            method="GET"
            role="search">
            <span class="material-symbols-rounded site-search-icon" aria-hidden="true">search</span>
            <label class="visually-hidden" for="site-search-input">Buscar productos</label>
            <input
                id="site-search-input"
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Buscar producto o marca…"
                autocomplete="off">
            <button type="submit" class="site-search-submit">Buscar</button>
        </form>

        {{-- Isla derecha: cotizar por WhatsApp --}}
        <a class="site-quote"
            href="https://wa.me/{{ env('WA_CONTACT', '59170000000') }}?text={{ urlencode('Hola Ewiflor, quiero cotizar un equipo.') }}"
            target="_blank"
            rel="noopener">
            <img src="{{ asset('images/whatsapp-logo.svg') }}" alt="" width="22" height="22" aria-hidden="true">
            <span>Cotizar</span>
        </a>

    </div>
</header>

<nav class="site-mainnav" id="site-navbar" aria-label="Navegación principal">
    <div class="container-main site-mainnav-inner">

        <div class="site-mainnav-links">
            <a href="{{ route('catalogo.publico') }}" class="site-nav-link {{ $esInicio ? 'active' : '' }}">Inicio</a>
            <a href="{{ $base }}#categorias" class="site-nav-link">Categorías</a>
            <a href="{{ route('publico.catalogo') }}" class="site-nav-link {{ $enCatalogo ? 'active' : '' }}"
                @if($enCatalogo) aria-current="page" @endif>Catálogo</a>
            <a href="{{ $base }}#servicio" class="site-nav-link">Servicio</a>
            <a href="{{ route('publico.nosotros') }}" class="site-nav-link {{ $enNosotros ? 'active' : '' }}"
                @if($enNosotros) aria-current="page" @endif>Nosotros</a>
        </div>

        <div class="site-mainnav-actions">
            <button
                type="button"
                id="theme-toggle"
                class="site-icon-btn"
                aria-label="Cambiar tema"
                title="Cambiar tema">
                <span id="theme-icon" class="material-symbols-rounded">dark_mode</span>
            </button>

            <a href="{{ route('login') }}" class="site-staff-link">Acceso empleados</a>

            <details class="site-mobile-menu">
                <summary class="site-icon-btn" aria-label="Abrir menú">
                    <span class="material-symbols-rounded">menu</span>
                </summary>

                <div class="site-mobile-panel">
                    <a href="{{ route('catalogo.publico') }}" class="{{ $esInicio ? 'active' : '' }}">Inicio</a>
                    <a href="{{ $base }}#categorias">Categorías</a>
                    <a href="{{ route('publico.catalogo') }}" class="{{ $enCatalogo ? 'active' : '' }}">Catálogo</a>
                    <a href="{{ $base }}#servicio">Servicio</a>
                    <a href="{{ route('publico.servicio.tecnico') }}" class="{{ $enServicio ? 'active' : '' }}">Servicio técnico</a>
                    <a href="{{ route('publico.nosotros') }}" class="{{ $enNosotros ? 'active' : '' }}">Nosotros</a>
                    <span class="site-mobile-divider" aria-hidden="true"></span>
                    <a href="{{ route('login') }}" class="site-mobile-staff">Acceso empleados</a>
                </div>
            </details>
        </div>

    </div>
</nav>

<style>
    .visually-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        white-space: nowrap;
        border: 0;
    }

    /* ---------- Barra superior ---------- */
    .site-topbar {
        position: relative;
        z-index: 60;
        background: var(--bg);
        border-bottom: 1px solid var(--border);
    }

    .site-topbar-inner {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 28px;
        padding: 12px 0;
    }

    .site-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .site-brand-logo {
        width: 58px;
        height: 58px;
        object-fit: contain;
        flex-shrink: 0;
    }

    .site-brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1.1;
    }

    .site-brand-name {
        color: var(--text);
        font-size: 1.18rem;
        font-weight: 800;
        letter-spacing: -.035em;
    }

    .site-brand-sub {
        margin-top: 3px;
        color: var(--muted);
        font-size: .6rem;
        font-weight: 600;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .site-search {
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
        width: min(100%, 640px);
        justify-self: center;
        padding: 5px 5px 5px 16px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: var(--surface);
        transition: border-color .2s, box-shadow .2s, background .2s;
    }

    .site-search:focus-within {
        border-color: var(--green);
        background: var(--bg);
        box-shadow: 0 0 0 3px rgba(42, 152, 50, .12);
    }

    .site-search-icon {
        color: var(--muted);
        font-size: 21px;
        pointer-events: none;
    }

    .site-search input {
        flex: 1;
        min-width: 0;
        height: 42px;
        border: 0;
        outline: none;
        background: transparent;
        color: var(--text);
        font-size: .9rem;
    }

    .site-search input::placeholder {
        color: var(--muted);
    }

    .site-search-submit {
        flex-shrink: 0;
        min-height: 42px;
        padding: 0 20px;
        border: 0;
        border-radius: 999px;
        background: var(--green);
        color: #fff;
        font-size: .82rem;
        font-weight: 700;
        transition: background .2s, transform .2s;
    }

    .site-search-submit:hover {
        background: #23822B;
        transform: translateY(-1px);
    }

    .site-search-submit:active {
        transform: scale(.97);
    }

    .site-quote {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 50px;
        padding: 0 22px;
        border-radius: 999px;
        background: #25D366;
        color: #fff;
        font-size: .86rem;
        font-weight: 800;
        white-space: nowrap;
        box-shadow: 0 8px 22px rgba(37, 211, 102, .28);
        transition: transform .2s, box-shadow .2s, background .2s;
    }

    .site-quote:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(37, 211, 102, .38);
    }

    .site-quote:active {
        transform: scale(.97);
    }

    /* ---------- Navegación convencional ---------- */
    .site-mainnav {
        position: sticky;
        top: 0;
        z-index: 90;
        background: color-mix(in srgb, var(--bg) 88%, transparent);
        border-bottom: 1px solid var(--border);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        transition: box-shadow .25s, background .25s;
    }

    .site-mainnav.scrolled {
        box-shadow: 0 8px 26px rgba(0, 0, 0, .08);
    }

    .site-mainnav-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        min-height: 56px;
    }

    .site-mainnav-links {
        display: flex;
        align-items: center;
        gap: 4px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .site-mainnav-links::-webkit-scrollbar {
        display: none;
    }

    .site-nav-link {
        position: relative;
        display: inline-flex;
        align-items: center;
        min-height: 44px;
        padding: 0 14px;
        border-radius: 10px;
        color: var(--muted);
        font-size: .82rem;
        font-weight: 600;
        white-space: nowrap;
        transition: color .2s, background .2s;
    }

    .site-nav-link:hover {
        color: var(--green);
        background: var(--surface);
    }

    .site-nav-link.active {
        color: var(--green);
    }

    .site-nav-link.active::after {
        content: '';
        position: absolute;
        left: 14px;
        right: 14px;
        bottom: 6px;
        height: 2px;
        border-radius: 2px;
        background: var(--green);
    }

    .site-mainnav-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .site-icon-btn {
        display: grid;
        place-items: center;
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: var(--text);
        cursor: pointer;
        transition: background .2s, color .2s;
    }

    .site-icon-btn:hover {
        background: var(--surface);
        color: var(--green);
    }

    .site-icon-btn .material-symbols-rounded {
        font-size: 21px;
    }

    .site-staff-link {
        display: inline-flex;
        align-items: center;
        min-height: 38px;
        padding: 0 16px;
        border-radius: 999px;
        background: var(--blue);
        color: #fff;
        font-size: .74rem;
        font-weight: 700;
        transition: background .2s, transform .2s;
    }

    .site-staff-link:hover {
        background: var(--blue-dark);
        transform: translateY(-1px);
    }

    /* ---------- Menú móvil ---------- */
    .site-mobile-menu {
        display: none;
        position: relative;
    }

    .site-mobile-menu > summary {
        list-style: none;
        cursor: pointer;
    }

    .site-mobile-menu > summary::-webkit-details-marker {
        display: none;
    }

    .site-mobile-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        z-index: 120;
        display: grid;
        gap: 3px;
        width: min(88vw, 320px);
        padding: 10px;
        border: 1px solid var(--border);
        border-radius: 18px;
        background: var(--bg);
        box-shadow: var(--shadow);
    }

    .site-mobile-panel a {
        display: block;
        padding: 12px 14px;
        border-radius: 10px;
        color: var(--muted);
        font-size: .88rem;
        font-weight: 600;
    }

    .site-mobile-panel a:hover,
    .site-mobile-panel a.active {
        background: var(--surface);
        color: var(--green);
    }

    .site-mobile-divider {
        height: 1px;
        margin: 5px 0;
        background: var(--border);
    }

    .site-mobile-panel .site-mobile-staff {
        color: var(--green);
        font-weight: 700;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 1024px) {
        .site-topbar-inner {
            gap: 18px;
        }

        .site-brand-logo {
            width: 50px;
            height: 50px;
        }

        .site-staff-link {
            display: none;
        }
    }

    @media (max-width: 860px) {
        .site-topbar-inner {
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            padding: 10px 0;
        }

        .site-search {
            grid-column: 1 / -1;
            order: 3;
            width: 100%;
            max-width: none;
        }

        .site-brand-logo {
            width: 44px;
            height: 44px;
        }

        .site-brand-name {
            font-size: 1.02rem;
        }

        .site-quote {
            min-height: 44px;
            padding: 0 16px;
            font-size: .8rem;
        }
    }

    @media (max-width: 767px) {
        .site-mainnav-links {
            display: none;
        }

        .site-mainnav-inner {
            justify-content: flex-end;
        }

        .site-mobile-menu {
            display: block;
        }
    }

    @media (max-width: 430px) {
        .site-brand-sub {
            display: none;
        }

        .site-search {
            padding-left: 12px;
        }

        .site-search-submit {
            padding: 0 14px;
        }
    }
</style>
