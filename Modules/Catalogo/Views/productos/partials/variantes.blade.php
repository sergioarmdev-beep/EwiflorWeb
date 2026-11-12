{{--
    Sección de variantes del producto (SKU, color, características).
    Variables: $numero (texto del paso, ej. "05"), $variantes (colección de VarianteProducto, opcional)
--}}
@php
    $filas = old('variantes');

    if ($filas === null) {
        $filas = isset($variantes)
            ? collect($variantes)->map(fn ($v) => [
                'id_variante' => $v->id_variante,
                'sku' => $v->sku,
                'color' => $v->color,
                'caracteristicas' => $v->caracteristicas,
                'estado' => $v->estado,
            ])->values()->all()
            : [];
    }
    $filas = array_values($filas);
@endphp

<section class="pt-8 border-t border-slate-800" id="variantes-section">

    <div class="flex items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-500/10 text-sky-400 text-xs font-bold">
                {{ $numero }}
            </span>

            <div>
                <h4 class="text-sm font-bold text-white">Variantes</h4>
                <p class="text-xs text-slate-500">
                    Colores o presentaciones con su propio SKU. Es opcional.
                </p>
            </div>
        </div>

        <button type="button" id="variante-add"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-sky-500/40 bg-sky-500/10 text-sky-300 hover:bg-sky-500/20 text-xs font-semibold transition duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Agregar variante
        </button>
    </div>

    @error('variantes')
    <span class="form-error mb-3">{{ $message }}</span>
    @enderror

    <div id="variantes-list" class="space-y-4">
        @foreach($filas as $i => $fila)
        @include('catalogo::productos.partials.variante-fila', ['i' => $i, 'fila' => $fila])
        @endforeach
    </div>

    <div id="variantes-vacio"
        class="{{ count($filas) ? 'hidden' : '' }} rounded-xl border border-dashed border-slate-700 bg-slate-950/50 px-6 py-8 text-center text-xs text-slate-500">
        Este producto no tiene variantes. Use «Agregar variante» si se ofrece en varios colores o presentaciones.
    </div>

    {{-- Plantilla para filas nuevas (__I__ se reemplaza por el índice) --}}
    <template id="variante-template">
        @include('catalogo::productos.partials.variante-fila', ['i' => '__I__', 'fila' => []])
    </template>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lista = document.getElementById('variantes-list');
        const vacio = document.getElementById('variantes-vacio');
        const plantilla = document.getElementById('variante-template');
        const btnAgregar = document.getElementById('variante-add');
        let siguiente = {{ count($filas) }};

        function actualizarVacio() {
            vacio.classList.toggle('hidden', lista.children.length > 0);
        }

        btnAgregar.addEventListener('click', () => {
            const html = plantilla.innerHTML.replaceAll('__I__', siguiente++);
            lista.insertAdjacentHTML('beforeend', html);
            actualizarVacio();
            lista.lastElementChild.querySelector('input[name$="[sku]"]')?.focus();
        });

        lista.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-variante-remove]');
            if (!btn) return;
            btn.closest('[data-variante-row]').remove();
            actualizarVacio();
        });
    });
</script>
