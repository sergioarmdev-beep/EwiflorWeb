    <!-- DETALLE DE PRODUCTO (MODAL) -->
    <div id="product-modal" class="pm" aria-hidden="true">
        <div class="pm-backdrop" data-pm-close></div>

        <div class="pm-dialog" role="dialog" aria-modal="true" aria-labelledby="pm-title" tabindex="-1">

            <button type="button" class="pm-close" data-pm-close aria-label="Cerrar detalle"><span class="material-symbols-rounded">close</span></button>

            <div class="pm-gallery">
                <div class="pm-main">
                    <img id="pm-image" alt="" hidden>
                    <div id="pm-placeholder" class="pm-placeholder" aria-hidden="true"><span class="material-symbols-rounded">inventory_2</span></div>

                    <button type="button" id="pm-prev" class="pm-nav pm-prev" aria-label="Imagen anterior"><span class="material-symbols-rounded">chevron_left</span></button>
                    <button type="button" id="pm-next" class="pm-nav pm-next" aria-label="Imagen siguiente"><span class="material-symbols-rounded">chevron_right</span></button>
                </div>

                <div id="pm-thumbs" class="pm-thumbs" aria-label="Miniaturas"></div>
            </div>

            <div class="pm-info">
                <span id="pm-brand" class="product-brand"></span>
                <h3 id="pm-title" class="pm-title"></h3>

                <div id="pm-tags" class="pm-tags"></div>

                <p id="pm-desc" class="pm-desc"></p>

                <dl id="pm-specs" class="pm-specs"></dl>

                <div id="pm-variants-wrap" hidden>
                    <h4 class="pm-subtitle">Variantes disponibles</h4>
                    <ul id="pm-variants" class="pm-variants"></ul>
                </div>

                <!-- Botón WhatsApp con contexto del producto -->
                <div class="pm-acciones" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); display: flex; gap: 10px;">
                    <a id="pm-ws-btn" href="#" class="button-whatsapp" target="_blank" rel="noopener"
                       style="flex: 1; justify-content: center; min-height: 48px; padding: 0 16px; font-size: 0.9rem;">
                        <img src="{{ asset('images/whatsapp-logo.svg') }}" alt="WhatsApp" width="18" height="18" style="display:inline-block; vertical-align:middle; margin-right:8px;">
                        Consultar por WhatsApp
                    </a>
                    <a href="{{ route('publico.catalogo') }}" class="button-secondary" style="flex: 1; justify-content: center; min-height: 48px; padding: 0 16px; font-size: 0.9rem;">
                        Volver al catálogo
                    </a>
                </div>
            </div>

        </div>
    </div>
