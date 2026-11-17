@extends('layouts.app')
@section('title', $modo === 'cotizacion' ? 'Convertir cotización a venta' : 'Nueva venta')

@section('content')
<div class="p-6 max-w-5xl">
    @if ($modo === 'cotizacion')
    <h1 class="text-2xl font-semibold mb-1">Convertir cotización #{{ $cotizacion->id_cotizacion }} a venta</h1>
    <p class="text-sm text-gray-500 mb-4">
        Cliente: <strong>{{ $cotizacion->cliente?->nombre }}</strong> ·
        T/C congelado: {{ number_format((float) $cotizacion->tipoCambio?->valor, 4) }} Bs/USD
    </p>
    @else
    <h1 class="text-2xl font-semibold mb-4">Nueva venta</h1>
    @endif

    @if ($errors->any())
    <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-3 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('ventas.store') }}" class="bg-white p-6 rounded shadow space-y-4">
        @csrf
        <input type="hidden" name="modo" value="{{ $modo }}">

        @if ($modo === 'cotizacion')
        <input type="hidden" name="id_cotizacion" value="{{ $cotizacion->id_cotizacion }}">

        <div>
            <label class="block text-sm font-medium">Almacén de salida *</label>
            <select name="id_almacen" required class="w-full border rounded px-3 py-2">
                <option value="">— Selecciona almacén —</option>
                @foreach ($almacenes as $a)
                <option value="{{ $a->id_almacen }}" @selected((string) old('id_almacen')===(string) $a->id_almacen)>
                    {{ $a->nombre }} — {{ $a->sucursal?->nombre ?? '' }}
                </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">
                Todas las líneas saldrán de este almacén. El stock se descuenta automáticamente.
            </p>
        </div>

        <div class="border rounded overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">SKU</th>
                        <th class="px-4 py-2 text-left">Producto</th>
                        <th class="px-4 py-2 text-right">Cant.</th>
                        <th class="px-4 py-2 text-right">P. Unit. USD</th>
                        <th class="px-4 py-2 text-right">Desc. %</th>
                        <th class="px-4 py-2 text-right">Subtotal USD</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cotizacion->detalles as $d)
                    @php
                    $sub = $d->cantidad * (float) $d->precio_unitario_usd
                    * (1 - (float) $d->descuento_pct / 100);
                    @endphp
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $d->variante?->sku ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $d->variante?->producto?->nombre ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">{{ $d->cantidad }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float) $d->precio_unitario_usd, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format((float) $d->descuento_pct, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($sub, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">Cliente *</label>
                <select name="id_cliente" id="id_cliente" required class="w-full border rounded px-3 py-2">
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
                <label class="block text-sm font-medium">Almacén de salida *</label>
                <select name="id_almacen" required class="w-full border rounded px-3 py-2">
                    <option value="">— Selecciona almacén —</option>
                    @foreach ($almacenes as $a)
                    <option value="{{ $a->id_almacen }}" @selected((string) old('id_almacen')===(string) $a->id_almacen)>
                        {{ $a->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <h2 class="font-semibold">Líneas de la venta</h2>
        <div class="overflow-x-auto rounded-lg bg-white shadow">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left min-w-[240px]">Variante</th>
                    <th class="px-3 py-2 text-right w-20">Cantidad</th>
                    <th class="px-3 py-2 text-right w-28">Precio USD</th>
                    <th class="px-3 py-2 text-right w-20">Garantía (meses)</th>
                    <th class="px-3 py-2 text-right w-24">Tipo desc.</th>
                    <th class="px-3 py-2 text-right w-28">Descuento</th>
                    <th class="px-3 py-2 text-right w-28">Subtotal USD</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody id="lineas-body" data-next="{{ count($lineas) }}">
                @foreach ($lineas as $i => $l)
                <tr>
                    <td class="px-3 py-2">
                        <x-variant-search
                            name="lineas[{{ $i }}][id_variante]"
                            :items="$variantesBusqueda"
                            :value="old('lineas.'.$i.'.id_variante', $l['id_variante'] ?? '')" />
                    </td>
                    <td class="px-3 py-2"><input type="number" name="lineas[{{ $i }}][cantidad]" min="1" required value="{{ $l['cantidad'] ?? 1 }}" class="w-full border rounded px-2 py-1 text-right"></td>
                    <td class="px-3 py-2">
                        <input type="number" step="0.01" readonly tabindex="-1"
                            name="lineas[{{ $i }}][precio_unitario_usd]"
                            value="{{ $l['precio_unitario_usd'] ?? '' }}" placeholder="—"
                            class="w-full border rounded px-2 py-1 text-right bg-gray-100 text-gray-600 cursor-not-allowed">
                    </td>
                    <td class="px-3 py-2"><input type="number" name="lineas[{{ $i }}][garantia_meses]" min="0" value="{{ $l['garantia_meses'] ?? $garantias[$l['id_variante']] ?? 0 }}" class="w-full border rounded px-2 py-1 text-right" placeholder="0"></td>
                    <td class="px-3 py-2">
                        <select name="lineas[{{ $i }}][descuento_tipo]" class="w-full border rounded px-2 py-1">
                            <option value="PCT" @selected(($l['descuento_tipo'] ?? 'PCT') === 'PCT')>%</option>
                            <option value="USD" @selected(($l['descuento_tipo'] ?? '') === 'USD')>USD</option>
                        </select>
                    </td>
                    <td class="px-3 py-2"><input type="number" name="lineas[{{ $i }}][descuento_valor]" step="0.01" min="0" value="{{ $l['descuento_valor'] ?? 0 }}" class="w-full border rounded px-2 py-1 text-right"></td>
                    <td class="px-3 py-2 text-right text-gray-600"><span data-subtotal>0.00</span></td>
                    <td class="px-3 py-2"><button type="button" class="remove text-red-600">✕</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <p class="text-xs text-gray-500">
            El precio se toma del catálogo y no puede editarse. La garantía por defecto viene del catálogo
            y puede editarse por línea. Solo se permite un descuento por línea,
            en porcentaje o en monto USD sobre el total de la línea.
            Si la variante tiene promoción vigente, el descuento se precarga al elegirla;
            el Vendedor no puede aplicar un descuento mayor al de la promoción (solo Gerente o Administrador).
            Si una variante aparece como «sin precio», un Gerente o Administrador debe asignarlo en Catálogo.
        </p>
        <button type="button" id="add-linea" class="text-blue-600 hover:underline text-sm">+ Agregar línea</button>

        <template id="linea-tpl">
            <tr>
                <td class="px-3 py-2">
                    <x-variant-search
                        name=""
                        :items="$variantesBusqueda"
                        value="" />
                </td>
                <td class="px-3 py-2"><input type="number" data-name="cantidad" min="1" value="1" required class="w-full border rounded px-2 py-1 text-right"></td>
                <td class="px-3 py-2">
                    <input type="number" data-name="precio_unitario_usd" step="0.01" readonly tabindex="-1"
                        placeholder="—" class="w-full border rounded px-2 py-1 text-right bg-gray-100 text-gray-600 cursor-not-allowed">
                </td>
                <td class="px-3 py-2"><input type="number" data-name="garantia_meses" min="0" value="0" class="w-full border rounded px-2 py-1 text-right" placeholder="0"></td>
                <td class="px-3 py-2">
                    <select data-name="descuento_tipo" class="w-full border rounded px-2 py-1">
                        <option value="PCT">%</option>
                        <option value="USD">USD</option>
                    </select>
                </td>
                <td class="px-3 py-2"><input type="number" data-name="descuento_valor" step="0.01" min="0" value="0" class="w-full border rounded px-2 py-1 text-right"></td>
                <td class="px-3 py-2 text-right text-gray-600"><span data-subtotal>0.00</span></td>
                <td class="px-3 py-2"><button type="button" class="remove text-red-600">✕</button></td>
            </tr>
        </template>
        @endif

        <div class="flex gap-2 pt-2">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                {{ $modo === 'cotizacion' ? 'Convertir a venta' : 'Registrar venta' }}
            </button>
            <a href="{{ route('ventas.index') }}" class="border px-4 py-2 rounded">Cancelar</a>
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