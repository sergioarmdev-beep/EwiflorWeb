    <header class="navbar" id="site-navbar">
        <div class="container-main navbar-inner">
            <nav class="nav-island" aria-label="Navegación principal">
                <a href="{{ $base }}#inicio" class="brand">
                    <span class="brand-icon">E</span>
                    <span><span class="brand-name">EWIFLOR</span><span class="brand-subtitle">Importaciones</span></span>
                </a>
                <div class="nav-links">
                    <a href="{{ route('catalogo.publico') }}" class="nav-link {{ $esInicio ? 'active' : '' }}">Inicio</a>
                    <a href="{{ $base }}#categorias" class="nav-link">Categorías</a>
                    <a href="{{ route('publico.catalogo') }}" class="nav-link {{ $enCatalogo ? 'active' : '' }}" data-page @if($enCatalogo) aria-current="page" @endif>Catálogo</a>
                    <a href="{{ route('publico.servicio.tecnico') }}" class="nav-link {{ $enServicio ? 'active' : '' }}" data-page @if($enServicio) aria-current="page" @endif>Servicio técnico</a>
                    <a href="{{ route('publico.nosotros') }}" class="nav-link {{ $enNosotros ? 'active' : '' }}" data-page @if($enNosotros) aria-current="page" @endif>Nosotros</a>
                </div>
                <button type="button" id="mobile-menu-toggle" class="mobile-menu-button" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobile-menu">
                    <span class="material-symbols-rounded">menu</span>
                </button>
            </nav>
            <div class="login-island">
                <button type="button" id="theme-toggle" class="theme-button" aria-label="Cambiar tema" title="Cambiar tema">
                    <span id="theme-icon" class="material-symbols-rounded">dark_mode</span>
                </button>
                <a href="{{ route('login') }}" class="employee-link desktop-employee-link">Acceso empleados</a>
            </div>
        </div>
        <div id="mobile-menu" class="mobile-menu container-main">
            <a href="{{ $base }}#inicio">Inicio</a><a href="{{ $base }}#categorias">Categorías</a><a href="{{ route('publico.catalogo') }}">Catálogo</a>
            <a href="{{ route('publico.servicio.tecnico') }}">Servicio técnico</a><a href="{{ route('publico.nosotros') }}">Nosotros</a>
            <a href="{{ route('login') }}">Acceso empleados</a>
        </div>
    </header>