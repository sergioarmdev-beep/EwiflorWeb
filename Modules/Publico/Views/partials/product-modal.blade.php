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
            </div>

        </div>
    </div>
