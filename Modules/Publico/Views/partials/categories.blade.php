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

    .category-more {
        display: flex;
        justify-content: center;
        margin-top: 34px;
    }

    /* Respaldo cuando la categoría todavía no tiene fotografía:
       degradado de marca + icono, nunca un hueco gris. */
    .category-fallback {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        background:
            radial-gradient(120% 100% at 12% 8%, rgba(130, 183, 35, .40), transparent 62%),
            linear-gradient(145deg, #22405F 0%, #16398A 58%, #0E2A4A 100%);
    }

    .category-fallback .material-symbols-rounded {
        color: rgba(255, 255, 255, .82);
        font-size: 84px;
        opacity: .9;
    }

    .category-card--fallback .category-overlay {
        background: linear-gradient(180deg,
                rgba(12, 20, 28, 0) 28%,
                rgba(12, 20, 28, .82) 100%);
    }
</style>
@endpush

<section id="categorias" class="section categories-section">
    <div class="container-main section-content">

        <div class="section-heading reveal">
            <span class="section-label">Explora nuestras áreas</span>

            <h2 class="section-title">
                ¿Qué negocio quiere <span class="gradient-text">equipar?</span>
            </h2>

            <p class="section-description">
                Elija el área de su negocio y vea los equipos disponibles para
                panadería, repostería, cocina y carnicería.
            </p>
        </div>

        @if($categorias->isEmpty())
        <div class="empty-catalog reveal">
            <span class="material-symbols-rounded" aria-hidden="true">category</span>
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
                $iconos = [
                    'ALIMENTARIA' => 'restaurant',
                    'PANADERIA'   => 'bakery_dining',
                    'REPOSTERIA'  => 'cake',
                    'CARNICA'     => 'set_meal',
                ];
                $slugIcono = $iconos[$cat['slug'] ?? ''] ?? 'category';
                $tieneImagen = ! empty($cat['imagen']);
            @endphp
            <{{ $tag }} class="category-card reveal {{ $tieneImagen ? '' : 'category-card--fallback' }}" @if($enlace) href="{{ $enlace }}" aria-label="Ver {{ $cat['titulo'] }} en el catálogo" @endif>
                @if($tieneImagen)
                <img
                    src="{{ $cat['imagen'] }}"
                    alt="Referencia de {{ $cat['titulo'] }}"
                    loading="lazy"
                    decoding="async"
                    onerror="this.style.display='none'">
                @endif
                @if(! $tieneImagen)
                <div class="category-fallback" aria-hidden="true">
                    <span class="material-symbols-rounded">{{ $slugIcono }}</span>
                </div>
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

        <div class="category-more reveal">
            <a href="{{ route('publico.catalogo') }}" class="button-primary">
                Ver todo el catálogo
                <span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span>
            </a>
        </div>

    </div>
</section>
