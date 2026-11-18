    <footer class="footer">
        <div class="container-main">

            <div class="footer-grid">

                <div class="footer-brand-column">
                    <a href="#inicio" class="brand">
                        <div class="brand-icon">E</div>
                        <div>
                            <span class="brand-name">EWIFLOR</span>
                            <span class="brand-subtitle">Importaciones</span>
                        </div>
                    </a>

                    <p class="footer-description">
                        Maquinaria y equipamiento para el sector gastronómico.
                        Conoce nuestras referencias y explora nuestro catálogo.
                    </p>
                </div>

                <div class="footer-column">
                    <h4>Navegación</h4>
                    <a href="#inicio">Inicio</a>
                    <a href="#categorias">Categorías</a>
                    <a href="#catalogo">Catálogo</a>
                    <a href="#contacto">Contacto</a>
                    <a href="#nosotros">Nosotros</a>
                </div>

                <div class="footer-column">
                    <h4>Acceso</h4>
                    <a href="{{ route('login') }}">Acceso empleados</a>
                    <a href="#catalogo">Productos</a>
                </div>

            </div>

            <div class="footer-bottom">
                <span>
                    © {{ date('Y') }} Ewiflor Importaciones. Todos los derechos reservados.
                </span>

                <span>
                    Sitio web de presentación.
                </span>
            </div>

        </div>
    </footer>
