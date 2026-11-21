@extends('publico::layouts.public')

@section('title', '{{ $producto->nombre }} | Ewiflor Importaciones')

@push('head')
    <!-- Meta tags Open Graph para este producto -->
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $producto->nombre }} - Ewiflor Importaciones">
    <meta property="og:description" content="{{ $producto->descripcion ?: 'Equipamiento gastronómico profesional de ' . ($producto->marca?->nombre ?? '') }}.">
    <meta property="og:url" content="{{ route('publico.producto.detalle', $producto->id_producto) }}">
    @if($producto->imagenes->first())
        <meta property="og:image" content="{{ $producto->imagenes->first()->url }}">
    @endif
    <meta property="og:site_name" content="Ewiflor Importaciones">
    <meta property="product:brand" content="{{ $producto->marca?->nombre ?? '' }}">
    <meta property="product:model" content="{{ $producto->modelo ?? '' }}">
@endpush

@section('content')

    <!-- Últimos productos vistos (carrusel dinámico) -->
    <section id="ultimos-vistos" class="section vista-reciente-section">
        <div class="container-main section-content">
            <div class="section-heading reveal" style="text-align: left; margin-bottom: 24px;">
                <span class="section-label" style="display: inline-block; margin-bottom: 10px;">Visto recientemente</span>
                <h2 class="section-title" style="font-size: 1.5rem; text-align: left;">Productos que has visto</h2>
            </div>

            <div class="recientes-carousel-track" id="recientes-track">
                <!-- Se llena con JavaScript desde localStorage -->
            </div>

            <div class="recientes-nav">
                <button class="recientes-btn recientes-prev" aria-label="Anterior">‹</button>
                <button class="recientes-btn recientes-next" aria-label="Siguiente">›</button>
            </div>

            <p class="recientes-hint" id="recientes-empty" hidden style="color: var(--muted); font-size: 0.85rem; text-align: center; margin-top: 16px;">
                Los productos que veas aparecerán aquí.
            </p>
        </div>
    </section>

    <!-- Detalle del producto -->
    <section id="detalle-producto" class="section detalle-producto-section">
        <div class="container-main section-content">

            <div class="detalle-grid">
                <!-- Galería -->
                <div class="detalle-galeria">
                    <div class="detalle-imagen-principal">
                        @if($producto->imagenes->isNotEmpty())
                            <img
                                id="detalle-img-principal"
                                src="{{ $producto->imagenes->firstWhere('es_principal', true) ?? $producto->imagenes->first()->url }}"
                                alt="{{ $producto->nombre }}"
                                loading="lazy"
                                decoding="async"
                                style="width: 100%; height: auto; border-radius: 16px; object-fit: cover;">
                        @else
                            <div class="detalle-imagen-placeholder" style="display: grid; place-items: center; height: 400px; border: 1px dashed var(--border); border-radius: 16px; background: var(--surface-2); color: var(--muted);">
                                <span class="material-symbols-rounded" style="font-size: 48px;">inventory_2</span>
                            </div>
                        @endif

                        <!-- Miniaturas -->
                        @if($producto->imagenes->count() > 1)
                        <div class="detalle-miniaturas" style="display: flex; gap: 8px; margin-top: 12px; overflow-x: auto; padding-bottom: 8px;">
                            @foreach($producto->imagenes as $img)
                                <img
                                    src="{{ $img->url }}"
                                    alt="{{ $img->texto_alternativo ?: $producto->nombre }}"
                                    class="detalle-miniatura{{ $img->es_principal ? ' activa' : '' }}"
                                    onclick="document.getElementById('detalle-img-principal').src='{{ $img->url }}'; this.parentElement.querySelectorAll('.detalle-miniatura').forEach(e=>e.classList.remove('activa')); this.classList.add('activa');"
                                    style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px; border: 2px solid {{ $img->es_principal ? 'var(--green)' : 'transparent' }}; cursor: pointer; flex-shrink: 0; opacity: {{ $img->es_principal ? '1' : '0.7' }}; transition: all 0.2s;"
                                    onmouseover="this.style.opacity='1'"
                                    onmouseout="if(!this.classList.contains('activa')){this.style.opacity='0.7'}"
                                    loading="lazy">
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Información -->
                <div class="detalle-info">
                    <span class="detalle-categoria" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border: 1px solid color-mix(in srgb, var(--green) 30%, var(--border)); border-radius: 999px; background: color-mix(in srgb, var(--green) 10%, var(--surface)); color: var(--green); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px; display: inline-block;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">category</span>
                        {{ $producto->categoria }}
                    </span>

                    <h1 class="detalle-titulo" style="font-size: clamp(2rem, 4vw, 2.8rem); font-weight: 800; line-height: 1.15; margin: 0 0 14px; letter-spacing: -0.04em;">
                        {{ $producto->nombre }}
                    </h1>

                    <div class="detalle-marca-modelo" style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 20px;">
                        <div class="detalle-dato" style="flex: 1;">
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 4px;">Marca</div>
                            <div style="font-size: 1rem; font-weight: 700; color: var(--text);">{{ $producto->marca?->nombre ?? '—' }}</div>
                        </div>
                        @if($producto->modelo)
                        <div class="detalle-dato" style="flex: 1;">
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 4px;">Modelo</div>
                            <div style="font-size: 1rem; font-weight: 700; color: var(--text);">{{ $producto->modelo }}</div>
                        </div>
                        @endif
                        <div class="detalle-dato" style="flex: 1;">
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 4px;">Garantía</div>
                            <div style="font-size: 1rem; font-weight: 700; color: var(--text);">
                                @if($producto->garantia_meses)
                                    {{ $producto->garantia_meses }} meses
                                @else
                                    Según producto
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="detalle-descripcion" style="margin-bottom: 24px;">
                        <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 8px;">Descripción</div>
                        <p style="color: var(--muted); line-height: 1.8; font-size: 0.95rem; white-space: pre-line;">{{ $producto->descripcion ?: 'Consulta las características de este producto.' }}</p>
                    </div>

                    <!-- Variantes -->
                    @if($producto->variantes->isNotEmpty())
                    <div class="detalle-variantes" style="margin-bottom: 24px;">
                        <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 12px;">Variantes disponibles</div>
                        <ul class="detalle-variantes-lista" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                            @foreach($producto->variantes as $v)
                            <li style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid var(--border); border-radius: 12px; background: var(--bg); font-size: 0.9rem;">
                                <span class="detalle-variante-nombre" style="font-weight: 700; color: var(--text); min-width: 120px;">
                                    @if($v->color) {{ $v->color }} @else {{ $v->sku }} @endif
                                </span>
                                @if($v->caracteristicas)
                                <span class="detalle-variante-carac" style="color: var(--muted); font-size: 0.85rem; flex: 1;">{{ $v->caracteristicas }}</span>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Botones de acción -->
                    <div class="detalle-acciones" style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <a href="https://wa.me/{{ env('WA_CONTACT', '59170000000') }}?text={{ urlencode('Hola Ewiflor, me interesa el producto: ' . $producto->nombre . ' (Marca: ' . ($producto->marca?->nombre ?? '') . ', Modelo: ' . $producto->modelo . '). ¿Podrían darme más información?') }}" 
                           class="button-whatsapp" target="_blank" rel="noopener"
                           style="display: inline-flex; align-items: center; gap: 8px; min-height: 50px; padding: 0 24px;">
                            <img src="{{ asset('images/whatsapp-logo.svg') }}" alt="WhatsApp" width="20" height="20" style="display:inline-block; vertical-align:middle;">
                            Consultar por WhatsApp
                        </a>

                        <button type="button" class="button-secondary" onclick="history.back()" style="display: inline-flex; align-items: center; gap: 8px; min-height: 50px; padding: 0 24px;">
                            <span class="material-symbols-rounded">arrow_back</span>
                            Volver al catálogo
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        /* ===================== ÚLTIMOS PRODUCTOS VISTOS ===================== */
        const STORAGE_KEY = 'ewiflor_vistos_productos';
        const MAX_VISTOS = 8;

        const track = document.getElementById('recientes-track');
        const emptyMsg = document.getElementById('recientes-empty');
        const btnPrev = document.querySelector('.recientes-prev');
        const btnNext = document.querySelector('.recientes-next');

        let vistos = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

        // Agregar este producto a la lista de vistos (si no está ya)
        const productoId = {{ $producto->id_producto }};
        const productoNombre = '{{ addslashes($producto->nombre) }}';
        const productoMarca = '{{ addslashes($producto->marca?->nombre ?? '') }}';
        const productoImagen = '{{ addslashes($producto->imagenes->firstWhere('es_principal', true) ?? $producto->imagenes->first()?->url ?? '') }}';

        if (productoImagen) {
            vistos = vistos.filter(v => v.id !== productoId);
            vistos.unshift({ id: productoId, nombre: productoNombre, marca: productoMarca, imagen: productoImagen });
            vistos = vistos.slice(0, MAX_VISTOS);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(vistos));
        }

        function renderRecientes() {
            if (!track) return;
            track.innerHTML = '';

            if (vistos.length === 0) {
                if (emptyMsg) emptyMsg.hidden = false;
                return;
            }
            if (emptyMsg) emptyMsg.hidden = true;

            vistos.forEach(p => {
                const card = document.createElement('article');
                card.className = 'reciente-card';
                card.style.cssText = `
                    flex: 0 0 auto;
                    width: 190px;
                    display: flex;
                    flex-direction: column;
                    gap: 8px;
                `;
                card.innerHTML = `
                    <div style="position: relative; width: 100%; aspect-ratio: 4/3; overflow: hidden; border-radius: 12px; background: var(--surface-2);">
                        <img src="${p.imagen}" alt="${p.nombre}" style="width: 100%; height: 100%; object-fit: cover; display: block;" loading="lazy" onerror="this.style.display='none'; this.parentElement.innerHTML='<div style=\\'width:100%;height:100%;display:grid;place-items:center;background:var(--surface-2);color:var(--muted)\\'><span class=\\'material-symbols-rounded\\' style=\\'font-size:32px\\'>inventory_2</span></div>';">
                    </div>
                    <div style="font-weight: 700; font-size: 0.88rem; color: var(--text); line-height: 1.3;">${p.nombre}</div>
                    ${p.marca ? `<div style="font-size: 0.78rem; color: var(--muted);">${p.marca}</div>` : ''}
                `;
                card.addEventListener('click', () => {
                    window.location.href = '{{ route('publico.producto.detalle', '__ID__') }}'.replace('__ID__', p.id);
                });
                card.style.cursor = 'pointer';
                track.appendChild(card);
            });
        }

        renderRecientes();

        /* ===================== CARRUSEL MOVIMIENTO CONSTANTE ===================== */
        let scrollPos = 0;
        const scrollStep = 1;
        const scrollInterval = 25;

        function autoScroll() {
            if (!track) return;
            const maxScroll = track.scrollWidth - track.clientWidth;
            if (maxScroll <= 0) return;

            scrollPos += scrollStep;
            if (scrollPos >= maxScroll) {
                scrollPos = 0; // Reinicia al inicio para movimiento constante
            }
            track.scrollLeft = scrollPos;
        }

        const scrollTimer = setInterval(autoScroll, scrollInterval);

        // Pausar al pasar el mouse
        track?.addEventListener('mouseenter', () => clearInterval(scrollTimer));
        track?.addEventListener('mouseleave', () => {
            // Reiniciar el temporizador al salir
        });

        btnPrev?.addEventListener('click', () => {
            track.scrollBy({ left: -200, behavior: 'smooth' });
        });

        btnNext?.addEventListener('click', () => {
            track.scrollBy({ left: 200, behavior: 'smooth' });
        });

        /* ===================== WHATSAPP DESDE BOTONES (contexto del producto) ===================== */
        document.querySelectorAll('[data-ws-producto]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = btn.dataset.wsProductoId;
                const nombre = btn.dataset.wsProductoNombre;
                const marca = btn.dataset.wsProductoMarca || '';
                const modelo = btn.dataset.wsProductoModelo || '';
                const msg = `Hola Ewiflor, me interesa el producto: ${nombre} (Marca: ${marca}, Modelo: ${modelo}). ¿Podrían darme más información?`;
                const waUrl = `https://wa.me/{{ env('WA_CONTACT', '59170000000') }}?text=${encodeURIComponent(msg)}`;
                window.open(waUrl, '_blank');
            });
        });
    });
</script>
@endpush
