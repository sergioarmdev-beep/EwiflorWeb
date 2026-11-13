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
                <select name="id_cliente" required class="w-full border rounded px-3 py-2">
                    <option value="">— Selecciona cliente —</option>
                    @foreach ($clientes as $c)
                    <option value="{{ $c->id_cliente }}" @selected((string) old('id_cliente')===(string) $c->id_cliente)>
                        {{ $c->nombre }} — {{ $c->telefono }}
                    </option>
                    @endforeach
                </select>
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
        <table class="w-full text-sm bg-white shadow rounded overflow-hidden">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">Variante</th>
                    <th class="px-3 py-2 text-right w-24">Cantidad</th>
                    <th class="px-3 py-2 text-right w-32">Precio USD</th>
                    <th class="px-3 py-2 text-right w-24">Desc. %</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody id="lineas-body" data-next="{{ count($lineas) }}">
                @foreach ($lineas as $i => $l)
                <tr>
                    <td class="px-3 py-2">
                        <select name="lineas[{{ $i }}][id_variante]" required class="w-full border rounded px-2 py-1">
                            <option value="">— Variante —</option>
                            @foreach ($variantes as $v)
                            <option value="{{ $v->id_variante }}"
                                @selected((string) ($l['id_variante'] ?? '' )===(string) $v->id_variante)>
                                {{ $v->sku }} — {{ $v->producto->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2"><input type="number" name="lineas[{{ $i }}][cantidad]" min="1" required value="{{ $l['cantidad'] ?? 1 }}" class="w-full border rounded px-2 py-1 text-right"></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" min="0" required name="lineas[{{ $i }}][precio_unitario_usd]" value="{{ $l['precio_unitario_usd'] ?? '' }}" class="w-full border rounded px-2 py-1 text-right"></td>
                    <td class="px-3 py-2"><input type="number" step="0.01" min="0" max="100" name="lineas[{{ $i }}][descuento_pct]" value="{{ $l['descuento_pct'] ?? 0 }}" class="w-full border rounded px-2 py-1 text-right"></td>
                    <td class="px-3 py-2"><button type="button" class="remove text-red-600">✕</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="button" id="add-linea" class="text-blue-600 hover:underline text-sm">+ Agregar línea</button>

        <template id="linea-tpl">
            <tr>
                <td class="px-3 py-2">
                    <select data-name="id_variante" required class="w-full border rounded px-2 py-1">
                        <option value="">— Variante —</option>
                        @foreach ($variantes as $v)
                        <option value="{{ $v->id_variante }}">{{ $v->sku }} — {{ $v->producto->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2"><input type="number" data-name="cantidad" min="1" value="1" required class="w-full border rounded px-2 py-1 text-right"></td>
                <td class="px-3 py-2"><input type="number" data-name="precio_unitario_usd" step="0.01" min="0" required class="w-full border rounded px-2 py-1 text-right"></td>
                <td class="px-3 py-2"><input type="number" data-name="descuento_pct" step="0.01" min="0" max="100" value="0" class="w-full border rounded px-2 py-1 text-right"></td>
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
        let idx = Number(tbody.dataset.next || 0);

        function bindRemove(tr) {
            tr.querySelector('.remove').addEventListener('click', () => tr.remove());
        }

        function addRow() {
            const tr = tpl.content.cloneNode(true).querySelector('tr');
            tr.querySelectorAll('[data-name]').forEach(el => {
                el.name = `lineas[${idx}][${el.dataset.name}]`;
            });
            bindRemove(tr);
            tbody.appendChild(tr);
            idx++;
        }
        document.getElementById('add-linea').addEventListener('click', addRow);
        tbody.querySelectorAll('tr').forEach(bindRemove);
        if (tbody.children.length === 0) addRow();
    })();
</script>
@endpush
@endsection