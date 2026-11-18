    <header class="navbar" id="site-navbar">
        <div class="container-main navbar-inner">
            <nav class="nav-island" aria-label="Navegación principal">
                <a href="#inicio" class="brand">
                    <span class="brand-icon">E</span>
                    <span><span class="brand-name">EWIFLOR</span><span class="brand-subtitle">Importaciones</span></span>
                </a>
                <div class="nav-links">
                    <a href="#inicio" class="nav-link active">Inicio</a>
                    <a href="#categorias" class="nav-link">Categorías</a>
                    <a href="#catalogo" class="nav-link">Catálogo</a>
                    <a href="#contacto" class="nav-link">Servicio técnico</a>
                    <a href="#nosotros" class="nav-link">Nosotros</a>
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
            <a href="#inicio">Inicio</a><a href="#categorias">Categorías</a><a href="#catalogo">Catálogo</a>
            <a href="#contacto">Servicio técnico</a><a href="#nosotros">Nosotros</a>
            <a href="{{ route('login') }}">Acceso empleados</a>
        </div>
    </header>
