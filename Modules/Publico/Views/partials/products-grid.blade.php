{{--
    Partial de productos. Dos modos (parámetro $modo):
      - 'inicio'   → vista previa (los primeros productos que envía el controller) + botón al catálogo completo.
      - 'completo' → página de catálogo: búsqueda + filtro por categoría (?categoria=slug) + contador.
--}}
@php
$completo = ($modo ?? 'completo') === 'completo';
$hayProductos = isset($productos) && $productos->isNotEmpty();
$totalProductos = $totalProductos ?? ($hayProductos ? $productos->count() : 0);
$categoriaActiva = $categoriaActiva ?? null;
@endphp

@push('head')
<style>
    .catalog-page {
        padding-top: 150px;
    }

    .catalog-filters {
        display: flex;
        gap: 10px;
        margin-bottom: 26px;
        padding: 2px 2px 12px;
        overflow-x: auto;
        scrollbar-width: thin;
    }

    .filter-chip {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 44px;
        padding: 0 16px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: var(--surface);
        color: var(--muted);
        font-size: .8rem;
        font-weight: 700;
        transition: background .2s, border-color .2s, color .2s;
    }

    .filter-chip:hover:not(:disabled) {
        border-color: var(--green);
        color: var(--green);
    }

    .filter-chip:focus-visible {
        outline: 3px solid var(--green);
        outline-offset: 2px;
    }

    .filter-chip[aria-pressed="true"] {
        border-color: var(--green);
        background: var(--green);
        color: #fff;
    }

    .filter-chip:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .chip-count {
        padding: 2px 8px;
        border-radius: 999px;
        background: color-mix(in srgb, var(--green) 12%, var(--bg));
        color: var(--green);
        font-size: .68rem;
    }

    .filter-chip[aria-pressed="true"] .chip-count {
        background: rgba(255, 255, 255, .22);
        color: #fff;
    }

    .catalog-more {
        display: flex;
        justify-content: center;
        margin-top: 38px;
    }

    @media (max-width: 760px) {
        .catalog-page {
            padding-top: 120px;
        }
    }
</style>
@endpush

<section id="catalogo" class="section catalog-section {{ $completo ? 'catalog-page' : '' }}">
    <div class="container-main section-content">

        <div class="catalog-header">
            <div class="reveal">
                <span class="section-label">Nuestros productos</span>

                <h2 class="section-title">
                    Catálogo <span class="gradient-text">{{ $completo ? 'completo' : 'disponible' }}</span>
                </h2>

                <p class="section-description">
                    @if($completo)
                    Filtra por categoría o busca por nombre, marca o modelo para encontrar el equipo que necesitas.
                    @else
                    Una selección de nuestra maquinaria y equipamiento.
                    @endif
                </p>
            </div>

            @if($completo && $hayProductos)
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
                    {{ $totalProductos }} {{ \Illuminate\Support\Str::plural('producto', $totalProductos) }} disponibles
                </p>
            </div>
            @endif
        </div>

        @if($hayProductos)

        @if($completo)
        <div class="catalog-filters reveal" role="group" aria-label="Filtrar por categoría">
            <button type="button" class="filter-chip" data-categoria="" data-titulo="Todas las categorías"
                aria-pressed="{{ $categoriaActiva ? 'false' : 'true' }}">
                Todas <span class="chip-count">{{ $totalProductos }}</span>
            </button>

            @foreach($categorias as $cat)
            <button type="button" class="filter-chip"
                data-categoria="{{ $cat['slug'] }}"
                data-titulo="{{ $cat['titulo'] }}"
                aria-pressed="{{ $categoriaActiva === $cat['slug'] ? 'true' : 'false' }}"
                {{ $cat['total'] === 0 ? 'disabled' : '' }}>
                {{ $cat['titulo'] }} <span class="chip-count">{{ $cat['total'] }}</span>
            </button>
            @endforeach
        </div>
        @endif

        <div id="product-grid" class="product-grid" data-categoria-inicial="{{ $categoriaActiva }}">
            @foreach($productos as $producto)
            @include('publico::partials.product-card', ['producto' => $producto, 'promociones' => $promociones])
            @endforeach
        </div>

        @if($completo)
        <div id="no-search-results" class="no-results" hidden>
            <span class="material-symbols-rounded" aria-hidden="true">search_off</span>
            <h3 style="font-weight: 800; color: var(--text); margin-bottom: 8px;">
                No encontramos resultados
            </h3>
            <p>Prueba con otro nombre, marca o categoría.</p>
            <button type="button" id="clear-filters" class="button-primary" style="margin-top: 18px;">
                Limpiar filtros
            </button>
        </div>
        @else
        <div class="catalog-more reveal">
            <a href="{{ route('publico.catalogo') }}" class="button-primary">
                Ver catálogo completo ({{ $totalProductos }}) <span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span>
            </a>
        </div>
        @endif

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

@if($completo && $hayProductos)
@push('scripts')
<script>
    (() => {
        const grid = document.getElementById('product-grid');
        if (!grid) return;

        const cards = Array.from(grid.querySelectorAll('.product-card[data-product-id]'));
        const chips = Array.from(document.querySelectorAll('.filter-chip'));
        const input = document.getElementById('product-search');
        const feedback = document.getElementById('search-results');
        const empty = document.getElementById('no-search-results');
        const clearBtn = document.getElementById('clear-filters');

        // Búsqueda sin distinguir mayúsculas ni tildes ("reposteria" encuentra "Repostería").
        const normalizar = (t) => (t || '')
            .toLocaleLowerCase('es')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');

        const indice = new Map(cards.map(c => [c, normalizar(c.dataset.search || c.textContent)]));
        const titulos = Object.fromEntries(chips.map(ch => [ch.dataset.categoria, ch.dataset.titulo]));

        let categoria = grid.dataset.categoriaInicial || '';

        const plural = (n) => `${n} ${n === 1 ? 'producto' : 'productos'}`;

        const aplicar = () => {
            const q = normalizar(input?.value.trim());
            let visibles = 0;

            cards.forEach(card => {
                const okCategoria = !categoria || card.dataset.categoria === categoria;
                const okTexto = !q || indice.get(card).includes(q);
                const mostrar = okCategoria && okTexto;
                card.hidden = !mostrar;
                if (mostrar) visibles++;
            });

            chips.forEach(ch => ch.setAttribute('aria-pressed', String(ch.dataset.categoria === categoria)));

            if (feedback) {
                const partes = [];
                if (categoria) partes.push(`en ${titulos[categoria]}`);
                if (q) partes.push(`para “${input.value.trim()}”`);
                feedback.textContent = partes.length ?
                    `${plural(visibles)} ${partes.join(' ')}` :
                    `${plural(visibles)} disponibles`;
            }

            if (empty) empty.hidden = visibles !== 0;

            // Mantiene la categoría en la URL (se puede compartir y funciona con "atrás").
            const url = new URL(window.location.href);
            if (categoria) url.searchParams.set('categoria', categoria.toLowerCase());
            else url.searchParams.delete('categoria');
            window.history.replaceState(null, '', url);
        };

        chips.forEach(ch => ch.addEventListener('click', () => {
            if (ch.disabled) return;
            categoria = ch.dataset.categoria;
            aplicar();
        }));

        input?.addEventListener('input', aplicar);

        clearBtn?.addEventListener('click', () => {
            categoria = '';
            if (input) input.value = '';
            aplicar();
            input?.focus();
        });

        aplicar();
    })();
</script>
@endpush
@endif
