                    @php
                    $principal = $producto->imagenes->firstWhere('es_principal', true)
                    ?? $producto->imagenes->first();

                    $textoBusqueda = mb_strtolower(implode(' ', array_filter([
                    $producto->nombre,
                    $producto->marca?->nombre,
                    $producto->modelo,
                    $producto->categoria,
                    $producto->descripcion,
                    ])));

                    // Promoción vigente de alguna variante del producto.
                    $promoProducto = null;

                    foreach ($producto->variantes as $v) {
                    if ($promo = ($promociones[$v->id_variante] ?? null)) {
                    $promoProducto = $promo;
                    break;
                    }
                    }

                    $esCombo = $producto->tipo_producto === 'COMBO';

                    $componentesCombo = $esCombo
                    ? $producto->componentesCombo
                    ->map(fn ($c) => ($c->componente?->nombre ?? 'Producto #' . $c->id_componente) . ' ×' . $c->cantidad)
                    ->implode(', ')
                    : null;
                    @endphp

                    <article class="product-card reveal"
                        data-product-id="{{ $producto->id_producto }}"
                        data-search="{{ $textoBusqueda }}"
                        role="button"
                        tabindex="0"
                        aria-haspopup="dialog"
                        aria-label="Ver detalle de {{ $producto->nombre }}">

                        <div class="product-image">

                            @if($principal)
                            <img
                                src="{{ $principal->url }}"
                                alt="{{ $principal->texto_alternativo ?: $producto->nombre }}"
                                loading="lazy"
                                decoding="async"
                                onerror="this.style.display='none'">
                            @else
                            <div class="product-image-placeholder" aria-hidden="true"><span class="material-symbols-rounded">inventory_2</span></div>
                            @endif

                            @if($promoProducto)
                            <span class="promo-badge">
                                {{ $promoProducto['tipo'] === 'PCT' ? 'OFERTA −'.rtrim(rtrim(number_format((float) $promoProducto['pct'], 2, '.', ''), '0'), '.').' %' : 'OFERTA' }}
                            </span>
                            @endif

                            @if($producto->imagenes->count() > 1)
                            <span class="product-photos" aria-hidden="true">{{ $producto->imagenes->count() }} fotos</span>
                            @endif

                        </div>

                        <div class="product-content">

                            @if($producto->marca)
                            <span class="product-brand">
                                {{ $producto->marca->nombre }}
                            </span>
                            @endif

                            <h3 class="product-title">
                                {{ $producto->nombre }}
                            </h3>

                            @if($esCombo)
                            <span class="combo-chip" style="margin-top: 8px;">COMBO</span>
                            @if($componentesCombo)
                            <span class="combo-componentes">
                                Incluye: {{ $componentesCombo }}
                            </span>
                            @endif
                            @endif

                            <p class="product-description">
                                {{ $producto->descripcion ?: 'Consulta las características de este producto.' }}
                            </p>

                            <div class="product-meta">

                                <span class="warranty">
                                    <span class="material-symbols-rounded" aria-hidden="true">verified</span>
                                    @if($producto->garantia_meses)
                                    {{ $producto->garantia_meses }} meses de garantía
                                    @else
                                    Garantía según producto
                                    @endif
                                </span>

                                <span class="product-more">Ver detalle <span class="material-symbols-rounded">arrow_forward</span></span>

                            </div>

                        </div>
                    </article>