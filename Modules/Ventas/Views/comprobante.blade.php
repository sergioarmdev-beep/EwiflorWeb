<!DOCTYPE html>
<html lang="es" class="h-full" data-theme="light">

<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $venta->numero_factura }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Aplica el tema antes de pintar para evitar flash --}}
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.dataset.theme = saved || (systemDark ? 'dark' : 'light');
        })();
    </script>

    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            max-width: 720px;
            margin: 30px auto;
            color: var(--text);
            background: var(--bg);
        }

        h1 {
            font-size: 22px;
            margin: 0 0 4px;
            color: var(--text);
        }

        .muted {
            color: var(--muted);
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            font-size: 13px;
        }

        th,
        td {
            border-bottom: 1px solid var(--border);
            padding: 6px 8px;
            text-align: left;
            color: var(--text);
        }

        th {
            background: var(--surface-2);
        }

        .right {
            text-align: right;
        }

        .total td {
            font-weight: 600;
            border-top: 2px solid var(--text);
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                background: var(--bg);
                color: var(--text);
            }
        }
    </style>
</head>

<body class="antialiased">
    <div class="no-print" style="margin-bottom:12px">
        <button onclick="window.print()" class="bg-brand-green text-white px-4 py-2 rounded-xl font-semibold hover:bg-brand-green-dark transition-colors">Imprimir</button>
        <a href="{{ route('ventas.show', $venta) }}" class="text-brand-blue hover:underline ml-2">← Volver a la venta</a>
    </div>

    <h1>Ewiflor Importaciones</h1>
    <p class="muted">NOTA DE VENTA — {{ $venta->numero_factura }}</p>
    <p class="muted">
        Fecha: {{ $venta->fecha_hora?->format('d/m/Y H:i') }} ·
        Sucursal: {{ $venta->sucursal?->nombre }} ·
        Vendedor: {{ $venta->usuario?->nombre_completo }}
    </p>
    <p>
        <strong>Cliente:</strong> {{ $venta->cliente?->nombre }}<br>
        <strong>Teléfono:</strong> {{ $venta->cliente?->telefono }}
    </p>

    <table>
        <thead>
            <tr>
                <th scope="col">SKU</th>
                <th scope="col">Producto</th>
                <th scope="col" class="right">Cant.</th>
                <th scope="col" class="right">P. Unit. USD</th>
                <th scope="col" class="right">Desc. %</th>
                <th scope="col" class="right">Garantía (meses)</th>
                <th scope="col" class="right">Subtotal USD</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venta->detalles as $d)
            <tr>
                <td>{{ $d->variante?->sku }}</td>
                <td>{{ $d->variante?->producto?->nombre }}</td>
                <td class="right">{{ $d->cantidad }}</td>
                <td class="right">{{ number_format((float) $d->precio_unitario_usd, 2) }}</td>
                <td class="right">{{ number_format((float) $d->descuento_pct, 2) }}</td>
                <td class="right">
                    @php
                        $gm = $d->garantia_meses;
                        if ($gm === null && $d->variante?->producto) {
                            $gm = $d->variante->producto->garantia_meses;
                        }
                    @endphp
                    {{ $gm ?? '—' }}
                </td>
                <td class="right">{{ number_format($d->subtotalUsd(), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="6" class="right">Total USD</td>
                <td class="right">$ {{ number_format($totales->total_usd ?? 0, 2) }}</td>
            </tr>
            <tr class="total">
                <td colspan="6" class="right">Total Bs (T/C {{ number_format((float) $venta->tipoCambio?->valor, 4) }})</td>
                <td class="right">Bs {{ number_format($totales->total_bs ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="muted" style="margin-top:20px">
        Gracias por su compra. Garantía sujeta a las condiciones del producto.
    </p>
</body>

</html>
