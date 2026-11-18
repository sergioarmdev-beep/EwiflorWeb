@push('head')
<style>
    /* 4 categorías caben mejor con auto-fit que con 3 columnas fijas */
    .category-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }

    a.category-card,
    .category-card {
        display: block;
    }

    a.category-card:focus-visible {
        outline: 3px solid var(--green);
        outline-offset: 3px;
    }

    .category-count .material-symbols-rounded {
        margin-left: 6px;
        font-size: 15px;
    }

    .category-overlay .category-count {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        margin-top: 10px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .18);
        color: #fff;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .04em;
        backdrop-filter: blur(6px);
    }
</style>
@endpush

<section id="categorias" class="section categories-section">
    <div class="container-main section-content">

        <div class="section-heading reveal">
            <span class="section-label">Explora nuestras referencias</span>

            <h2 class="section-title">
                Equipamiento para <span class="gradient-text">cada necesidad</span>
            </h2>

            <p class="section-description">
                Una muestra visual de las distintas áreas de equipamiento
                gastronómico que forman parte de nuestro catálogo.
            </p>
        </div>

        @if($categorias->isEmpty())
        <div class="empty-catalog reveal">
            <span class="material-symbols-rounded">category</span>
            Aún no hay categorías cargadas.
        </div>
        @else
        <div class="category-grid">
            @foreach($categorias as $cat)
            @php
                $enlace = $cat['total'] > 0
                    ? route('publico.catalogo', ['categoria' => strtolower($cat['slug'])])
                    : null;
                $tag = $enlace ? 'a' : 'article';
            @endphp
            <{{ $tag }} class="category-card reveal" @if($enlace) href="{{ $enlace }}" aria-label="Ver {{ $cat['titulo'] }} en el catálogo" @endif>
                @if($cat['imagen'])
                <img
                    src="{{ $cat['imagen'] }}"
                    alt="Referencia de {{ $cat['titulo'] }}"
                    loading="lazy"
                    decoding="async"
                    onerror="this.style.display='none'">
                @endif

                <div class="category-overlay">
                    <h3>{{ $cat['titulo'] }}</h3>
                    <p>{{ $cat['descripcion'] }}</p>
                    <span class="category-count">
                        {{ $cat['total'] }} {{ \Illuminate\Support\Str::plural('producto', $cat['total']) }}
                        @if($enlace)<span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span>@endif
                    </span>
                </div>
            </{{ $tag }}>
            @endforeach
        </div>
        @endif

    </div>
</section>