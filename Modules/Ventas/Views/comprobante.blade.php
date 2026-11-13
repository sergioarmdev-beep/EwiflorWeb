<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $venta->numero_factura }}</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 720px;
            margin: 30px auto;
            color: #111;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 4px;
        }

        .muted {
            color: #666;
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
            border-bottom: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }

        .right {
            text-align: right;
        }

        .total td {
            font-weight: 600;
            border-top: 2px solid #333;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom:12px">
        <button onclick="window.print()">Imprimir</button>
        <a href="{{ route('ventas.show', $venta) }}">← Volver a la venta</a>
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
                <th>SKU</th>
                <th>Producto</th>
                <th class="right">Cant.</th>
                <th class="right">P. Unit. USD</th>
                <th class="right">Desc. %</th>
                <th class="right">Subtotal USD</th>
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
                <td class="right">{{ number_format($d->subtotalUsd(), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="5" class="right">Total USD</td>
                <td class="right">$ {{ number_format($totales->total_usd ?? 0, 2) }}</td>
            </tr>
            <tr class="total">
                <td colspan="5" class="right">Total Bs (T/C {{ number_format((float) $venta->tipoCambio?->valor, 4) }})</td>
                <td class="right">Bs {{ number_format($totales->total_bs ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="muted" style="margin-top:20px">
        Gracias por su compra. Garantía sujeta a las condiciones del producto.
    </p>
</body>

</html>