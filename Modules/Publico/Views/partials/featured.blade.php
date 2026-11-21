{{--
    Equipos destacados: los productos públicos con más unidades vendidas,
    reutilizando la misma tarjeta del catálogo.
--}}
<section id="destacados" class="section featured-section">
    <div class="container-main section-content">

        <div class="section-heading reveal">
            <span class="section-label">Los más solicitados</span>

            <h2 class="section-title">
                Equipos <span class="gradient-text">destacados</span>
            </h2>

            <p class="section-description">
                Los equipos que más eligen nuestros clientes para poner en marcha
                su negocio.
            </p>
        </div>

        @if(isset($destacados) && $destacados->isNotEmpty())
        <div class="product-grid">
            @foreach($destacados as $producto)
                @include('publico::partials.product-card', ['producto' => $producto, 'promociones' => $promociones])
            @endforeach
        </div>

        <div class="category-more reveal">
            <a href="{{ route('publico.catalogo') }}" class="button-primary">
                Ver catálogo completo
                <span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span>
            </a>
        </div>
        @else
        <div class="empty-catalog reveal">
            <span class="material-symbols-rounded" aria-hidden="true">inventory_2</span>
            Estamos actualizando los equipos destacados.
        </div>
        @endif

    </div>
</section>
