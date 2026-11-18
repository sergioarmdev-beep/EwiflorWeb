        <section id="catalogo" class="section catalog-section">
            <div class="container-main section-content">

                <div class="catalog-header">
                    <div class="reveal">
                        <span class="section-label">Nuestros productos</span>

                        <h2 class="section-title">
                            Catálogo <span class="gradient-text">disponible</span>
                        </h2>

                        <p class="section-description">
                            Explora nuestra selección de maquinaria y equipamiento.
                        </p>
                    </div>

                    <div class="search-wrapper reveal">
                        <div class="search-box">
                            <span class="material-symbols-rounded" aria-hidden="true">search</span>

                            <input
                                type="search"
                                id="product-search"
                                class="search-input"
                                placeholder="Buscar producto o marca..."
                                autocomplete="off"
                                aria-label="Buscar productos">
                        </div>

                        <p id="search-results" class="search-feedback" aria-live="polite">
                            Explora los productos disponibles.
                        </p>
                    </div>
                </div>

                @php
                $hayProductos = isset($productos) && $productos->isNotEmpty();
                @endphp

                @if($hayProductos)

                <div id="product-grid" class="product-grid">


                    @foreach($productos as $producto)
                        @include('publico::partials.product-card', ['producto' => $producto, 'promociones' => $promociones])
                    @endforeach

                </div>

                <div id="no-search-results" class="no-results">
                    <span class="material-symbols-rounded" aria-hidden="true">search_off</span>
                    <h3 style="font-weight: 800; color: var(--text); margin-bottom: 8px;">
                        No encontramos resultados
                    </h3>
                    <p>Prueba con otro nombre, marca o término de búsqueda.</p>
                </div>

                @else

                <div class="empty-catalog">
                    <span class="material-symbols-rounded" aria-hidden="true">inventory_2</span>

                    <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text);">
                        Próximamente nuestro catálogo
                    </h3>

                    <p style="margin-top: 10px;">
                        Estamos preparando la información de nuestros productos.
                    </p>
                </div>

                @endif

            </div>
        </section>
