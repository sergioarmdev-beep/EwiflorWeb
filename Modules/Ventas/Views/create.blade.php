@extends('layouts.app')
@section('title', $modo === 'cotizacion' ? 'Convertir cotización a venta' : 'Nueva venta')

@section('content')
<div class="p-6 max-w-5xl">
    @if ($modo === 'cotizacion')
    <h1 class="text-2xl font-bold text-ink mb-1">Convertir cotización #{{ $cotizacion->id_cotizacion }} a venta</h1>
    <p class="text-sm text-muted mb-4">
        Cliente: <strong class="text-ink">{{ $cotizacion->cliente?->nombre }}</strong> ·
        T/C congelado: {{ number_format((float) $cotizacion->tipoCambio?->valor, 4) }} Bs/USD
    </p>
    @else
    <h1 class="text-2xl font-bold text-ink mb-4">Nueva venta</h1>
    @endif

    @if ($errors->any())
    <div class="mb-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-500">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('ventas.store') }}" class="bg-surface p-6 rounded-2xl border border-line shadow-soft space-y-4">
        @csrf
        <input type="hidden" name="modo" value="{{ $modo }}">

        @if ($modo === 'cotizacion')
        <input type="hidden" name="id_cotizacion" value="{{ $cotizacion->id_cotizacion }}">

        <div>
            <label class="block text-sm font-medium text-muted">Almacén de salida *</label>
            <select name="id_almacen" required class="w-full border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">— Selecciona almacén —</option>
                @foreach ($almacenes as $a)
                <option value="{{ $a->id_almacen }}" @selected((string) old('id_almacen')===(string) $a->id_almacen)>
                    {{ $a->nombre }} — {{ $a->sucursal?->nombre ?? '' }}
                </option>
                @endforeach
            </select>
            <p class="text-xs text-muted mt-1">
                Todas las líneas saldrán de este almacén. El stock se descuenta automáticamente.
            </p>
        </div>

        <div class="border border-line rounded-xl overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-surface-2">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">SKU</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Producto</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Cant.</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">P. Unit. USD</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Desc. %</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Subtotal USD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($cotizacion->detalles as $d)
                    @php
                    $sub = $d->cantidad * (float) $d->precio_unitario_usd
                    * (1 - (float) $d->descuento_pct / 100);
                    @endphp
                    <tr>
                        <td class="px-4 py-2 text-ink">{{ $d->variante?->sku ?? '—' }}</td>
                        <td class="px-4 py-2 text-ink">{{ $d->variante?->producto?->nombre ?? '—' }}</td>
                        <td class="px-4 py-2 text-right text-ink">{{ $d->cantidad }}</td>
                        <td class="px-4 py-2 text-right text-ink">{{ number_format((float) $d->precio_unitario_usd, 2) }}</td>
                        <td class="px-4 py-2 text-right text-ink">{{ number_format((float) $d->descuento_pct, 2) }}</td>
                        <td class="px-4 py-2 text-right text-ink">{{ number_format($sub, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-muted">Cliente *</label>
                <select name="id_cliente" id="id_cliente" required class="w-full border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-brand-green/20">
                    <option value="">— Selecciona cliente —</option>
                    @foreach ($clientes as $c)
                    <option value="{{ $c->id_cliente }}" @selected((string) old('id_cliente')===(string) $c->id_cliente)>
                        {{ $c->nombre }} — {{ $c->telefono }}
                    </option>
                    @endforeach
                </select>

                @include('clientes::partials.quick-create')
            </div>
            <div>
                <label class="block text-sm font-medium text-muted">Almacén de salida *</label>
                <select name="id_almacen" required class="w-full border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-brand-green/20">
                    <option value="">— Selecciona almacén —</option>
                    @foreach ($almacenes as $a)
                    <option value="{{ $a->id_almacen }}" @selected((string) old('id_almacen')===(string) $a->id_almacen)>
                        {{ $a->nombre }} — {{ $a->sucursal?->nombre ?? '' }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-muted mt-1">
                    La venta se registra en la sucursal del almacén elegido.
                </p>
            </div>
        </div>

        <h2 class="font-bold text-ink">Líneas de la venta</h2>
        <div class="overflow-x-auto rounded-xl bg-surface border border-line shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-surface-2">
                <tr>
                    <th class="px-3 py-2 text-left min-w-[240px] text-xs font-semibold uppercase tracking-wider text-muted">Variante</th>
                    <th class="px-3 py-2 text-right w-20 text-xs font-semibold uppercase tracking-wider text-muted">Cantidad</th>
                    <th class="px-3 py-2 text-right w-28 text-xs font-semibold uppercase tracking-wider text-muted">Precio USD</th>
                    <th class="px-3 py-2 text-right w-20 text-xs font-semibold uppercase tracking-wider text-muted">Garantía (meses)</th>
                    <th class="px-3 py-2 text-right w-24 text-xs font-semibold uppercase tracking-wider text-muted">Tipo desc.</th>
                    <th class="px-3 py-2 text-right w-28 text-xs font-semibold uppercase tracking-wider text-muted">Descuento</th>
                    <th class="px-3 py-2 text-right w-28 text-xs font-semibold uppercase tracking-wider text-muted">Subtotal USD</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody id="lineas-body" data-next="{{ count($lineas) }}" class="divide-y divide-line">
                @foreach ($lineas as $i => $l)
                <tr>
                    <td class="px-3 py-2">
                        <x-variant-search
                            name="lineas[{{ $i }}][id_variante]"
                            :items="$variantesBusqueda"
                            :value="old('lineas.'.$i.'.id_variante', $l['id_variante'] ?? '')" />
                    </td>
                    <td class="px-3 py-2"><input type="number" name="lineas[{{ $i }}][cantidad]" min="1" required value="{{ $l['cantidad'] ?? 1 }}" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20"></td>
                    <td class="px-3 py-2">
                        <input type="number" step="0.01" readonly tabindex="-1"
                            name="lineas[{{ $i }}][precio_unitario_usd]"
                            value="{{ $l['precio_unitario_usd'] ?? '' }}" placeholder="—"
                            class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right cursor-not-allowed">
                    </td>
                    <td class="px-3 py-2"><input type="number" name="lineas[{{ $i }}][garantia_meses]" min="0" value="{{ $l['garantia_meses'] ?? $garantias[$l['id_variante']] ?? 0 }}" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20" placeholder="0"></td>
                    <td class="px-3 py-2">
                        <select name="lineas[{{ $i }}][descuento_tipo]" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 focus:border-brand-green/50 focus:ring-brand-green/20">
                            <option value="PCT" @selected(($l['descuento_tipo'] ?? 'PCT') === 'PCT')>%</option>
                            <option value="USD" @selected(($l['descuento_tipo'] ?? '') === 'USD')>USD</option>
                        </select>
                    </td>
                    <td class="px-3 py-2"><input type="number" name="lineas[{{ $i }}][descuento_valor]" step="0.01" min="0" value="{{ $l['descuento_valor'] ?? 0 }}" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20"></td>
                    <td class="px-3 py-2 text-right text-muted"><span data-subtotal>0.00</span></td>
                    <td class="px-3 py-2"><button type="button" class="remove text-red-500">✕</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <p class="text-xs text-muted">
            El precio se toma del catálogo y no puede editarse. La garantía por defecto viene del catálogo
            y puede editarse por línea. Solo se permite un descuento por línea,
            en porcentaje o en monto USD sobre el total de la línea.
            Si la variante tiene promoción vigente, el descuento se precarga al elegirla;
            el Vendedor no puede aplicar un descuento mayor al de la promoción (solo Gerente o Administrador).
            Si una variante aparece como «sin precio», un Gerente o Administrador debe asignarlo en Catálogo.
        </p>
        <button type="button" id="add-linea" class="text-brand-blue hover:underline text-sm">+ Agregar línea</button>

        <template id="linea-tpl">
            <tr>
                <td class="px-3 py-2">
                    <x-variant-search
                        name=""
                        :items="$variantesBusqueda"
                        value="" />
                </td>
                <td class="px-3 py-2"><input type="number" data-name="cantidad" min="1" value="1" required class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20"></td>
                <td class="px-3 py-2">
                    <input type="number" data-name="precio_unitario_usd" step="0.01" readonly tabindex="-1"
                        placeholder="—" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right cursor-not-allowed">
                </td>
                <td class="px-3 py-2"><input type="number" data-name="garantia_meses" min="0" value="0" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20" placeholder="0"></td>
                <td class="px-3 py-2">
                    <select data-name="descuento_tipo" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 focus:border-brand-green/50 focus:ring-brand-green/20">
                        <option value="PCT">%</option>
                        <option value="USD">USD</option>
                    </select>
                </td>
                <td class="px-3 py-2"><input type="number" data-name="descuento_valor" step="0.01" min="0" value="0" class="w-full border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 text-right focus:border-brand-green/50 focus:ring-brand-green/20"></td>
                <td class="px-3 py-2 text-right text-muted"><span data-subtotal>0.00</span></td>
                <td class="px-3 py-2"><button type="button" class="remove text-red-500">✕</button></td>
            </tr>
        </template>
        @endif

        <div class="flex gap-2 pt-2">
            <button class="bg-brand-green text-white px-4 py-2 rounded-xl font-semibold hover:bg-brand-green-dark transition-colors">
                {{ $modo === 'cotizacion' ? 'Convertir a venta' : 'Registrar venta' }}
            </button>
            <a href="{{ route('ventas.index') }}" class="border border-line rounded-xl px-4 py-2 text-ink hover:bg-surface-2 transition-colors">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    (function() {
        const tbody = document.getElementById('lineas-body');
        if (!tbody) return;

        const tpl = document.getElementById('linea-tpl');
        const precios = @json($precios ?? []);
        const garantias = @json($garantias ?? []);
        const promociones = @json($promociones ?? []);
        const puedeEditarDescuento = @json($puedeEditarDescuento ?? true);
        let idx = Number(tbody.dataset.next || 0);

        const numero = (valor) => {
            const n = parseFloat(valor);
            return Number.isFinite(n) ? n : 0;
        };

        const campo = (tr, nombre) =>
            tr.querySelector(`[name$="[${nombre}]"], [data-name="${nombre}"]`);

        function bindRemove(tr) {
            tr.querySelector('.remove').addEventListener('click', () => tr.remove());
        }

        function precioDe(tr) {
            const id = campo(tr, 'id_variante')?.value;
            if (!id) return null;
            const precio = Number(precios[id] ?? 0);
            return precio > 0 ? precio : null;
        }

        function recalcular(tr) {
            const precio = precioDe(tr);
            const cantidad = Math.max(1, parseInt(campo(tr, 'cantidad')?.value || '1', 10));
            const tipo = campo(tr, 'descuento_tipo')?.value || 'PCT';
            const inputDescuento = campo(tr, 'descuento_valor');
            const descuento = Math.max(0, numero(inputDescuento?.value));

            let subtotal = 0;
            if (precio) {
                const bruto = precio * cantidad;
                if (tipo === 'USD') {
                    subtotal = Math.max(0, bruto - descuento);
                    if (inputDescuento) inputDescuento.max = bruto.toFixed(2);
                } else {
                    subtotal = bruto * (1 - Math.min(descuento, 100) / 100);
                    if (inputDescuento) inputDescuento.removeAttribute('max');
                }
            }

            const etiqueta = tr.querySelector('[data-subtotal]');
            if (etiqueta) etiqueta.textContent = subtotal.toFixed(2);
        }

        function aplicarPrecio(tr) {
            const input = campo(tr, 'precio_unitario_usd');
            if (!input) return;
            const precio = precioDe(tr);
            input.value = precio ? precio.toFixed(2) : '';
            input.title = precio ? '' : 'Esta variante no tiene precio de lista';
            recalcular(tr);
        }

        /**
         * Al cambiar de variante se propone la garantía por defecto del catálogo.
         * Solo se hace en el evento de cambio (nunca al cargar la página), para no
         * pisar una garantía editada previamente por el usuario.
         */
        function aplicarGarantia(tr) {
            const id = campo(tr, 'id_variante')?.value;
            const input = campo(tr, 'garantia_meses');
            if (!id || !input) return;
            input.value = Number(garantias[id] ?? 0);
        }

        /**
         * Al cambiar de variante se precarga el descuento de la promoción
         * vigente. Para VENDEDOR los controles quedan bloqueados: solo puede
         * aplicar el descuento de la promo (o ninguno), nunca más.
         */
        function aplicarPromo(tr) {
            const id = campo(tr, 'id_variante')?.value;
            const tipoSel = campo(tr, 'descuento_tipo');
            const inputDesc = campo(tr, 'descuento_valor');
            if (!id || !tipoSel || !inputDesc) return;

            const promo = promociones[id];

            if (promo) {
                tipoSel.value = promo.tipo;
                inputDesc.value = promo.valor;
                inputDesc.title = `Promoción vigente: ${promo.nombre}`;
            } else {
                inputDesc.value = 0;
                inputDesc.title = '';
            }

            if (!puedeEditarDescuento) {
                inputDesc.readOnly = true;
                [...tipoSel.options].forEach((o) => {
                    o.disabled = !promo || o.value !== promo.tipo;
                });
            } else {
                inputDesc.readOnly = false;
                [...tipoSel.options].forEach((o) => {
                    o.disabled = false;
                });
            }

            recalcular(tr);
        }

        function addRow() {
            const tr = tpl.content.cloneNode(true).querySelector('tr');
            tr.querySelectorAll('[data-name]').forEach(el => {
                el.name = `lineas[${idx}][${el.dataset.name}]`;
            });
            bindRemove(tr);
            tbody.appendChild(tr);
            idx++;
            aplicarPrecio(tr);
        }

        document.getElementById('add-linea').addEventListener('click', addRow);

        tbody.addEventListener('change', (e) => {
            const tr = e.target.closest('tr');
            if (!tr) return;
            if (e.target.matches('[name$="[id_variante]"], [data-name="id_variante"]')) {
                aplicarPrecio(tr);
                aplicarGarantia(tr);
                aplicarPromo(tr);
            } else {
                recalcular(tr);
            }
        });

        tbody.addEventListener('input', (e) => {
            const tr = e.target.closest('tr');
            if (!tr) return;
            if (e.target.matches('[name$="[cantidad]"], [name$="[descuento_valor]"], [data-name="cantidad"], [data-name="descuento_valor"]')) {
                recalcular(tr);
            }
        });

        tbody.querySelectorAll('tr').forEach(tr => {
            bindRemove(tr);
            aplicarPrecio(tr);
        });

        if (tbody.children.length === 0) addRow();
    })();
</script>
@endpush
@endsection
