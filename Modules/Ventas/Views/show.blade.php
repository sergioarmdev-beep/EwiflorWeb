@extends('layouts.app')
@section('title', "Venta {$venta->numero_factura}")

@section('content')
@php
$rol = auth()->user()->nombre_rol;
$puedeCancelar = in_array($rol, ['GERENTE','ADMINISTRADOR']) && $venta->estado !== 'CANCELADA';
$saldo = $venta->saldoBs();
@endphp

<div class="p-6 max-w-5xl space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">
            Venta {{ $venta->numero_factura }}
            <span class="ml-2 text-xs px-2 py-1 rounded
                {{ $venta->estado === 'REGISTRADA' ? 'bg-green-100 text-green-800' : '' }}
                {{ $venta->estado === 'EN_PROCESO' ? 'bg-amber-100 text-amber-800' : '' }}
                {{ $venta->estado === 'CANCELADA' ? 'bg-red-100 text-red-800' : '' }}">
                {{ $venta->estado }}
            </span>
        </h1>
        <div class="flex gap-2">
            <a href="{{ route('ventas.comprobante', $venta) }}" target="_blank"
                class="border px-4 py-2 rounded">Imprimir</a>
            @if ($puedeCancelar)
            <button type="button" onclick="document.getElementById('dlg-cancelar').showModal()"
                class="border border-red-500 text-red-600 px-4 py-2 rounded">Cancelar venta</button>
            @endif
            <a href="{{ route('ventas.index') }}" class="border px-4 py-2 rounded">Volver</a>
        </div>
    </div>

    @if (session('ok')) <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('ok') }}</div> @endif
    @if (session('error')) <div class="bg-red-100 text-red-800 px-4 py-2 rounded">{{ session('error') }}</div> @endif

    <div class="bg-white p-6 rounded shadow grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div><span class="text-gray-500">Cliente:</span> {{ $venta->cliente?->nombre ?? '—' }}</div>
        <div><span class="text-gray-500">Teléfono:</span> {{ $venta->cliente?->telefono ?? '—' }}</div>
        <div><span class="text-gray-500">Sucursal:</span> {{ $venta->sucursal?->nombre ?? '—' }}</div>
        <div><span class="text-gray-500">Vendedor:</span> {{ $venta->usuario?->nombre_completo ?? '—' }}</div>
        <div><span class="text-gray-500">Fecha:</span> {{ $venta->fecha_hora?->format('d/m/Y H:i') }}</div>
        <div><span class="text-gray-500">T/C aplicado:</span>
            {{ $venta->tipoCambio?->tipo }} — {{ number_format((float) $venta->tipoCambio?->valor, 4) }} Bs/USD
        </div>
        @if ($venta->estado === 'CANCELADA')
        <div class="md:col-span-3 p-3 bg-red-50 rounded text-red-800">
            Cancelada el {{ $venta->fecha_cancelacion?->format('d/m/Y H:i') }}
            por {{ $venta->usuarioCancela?->nombre_completo ?? '—' }}.
            Motivo: {{ $venta->motivo_cancelacion }}
        </div>
        @endif
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
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
                @foreach ($venta->detalles as $d)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $d->variante?->sku ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $d->variante?->producto?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2 text-right">{{ $d->cantidad }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format((float) $d->precio_unitario_usd, 2) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format((float) $d->descuento_pct, 2) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($d->subtotalUsd(), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 border-t font-semibold">
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right">Total USD</td>
                    <td class="px-4 py-2 text-right">$ {{ number_format($totales->total_usd ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right">Total Bs</td>
                    <td class="px-4 py-2 text-right">Bs {{ number_format($totales->total_bs ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right">Pagado</td>
                    <td class="px-4 py-2 text-right">Bs {{ number_format($venta->totalPagadoBs(), 2) }}</td>
                </tr>
                <tr class="{{ $saldo > 0 ? 'text-red-700' : 'text-green-700' }}">
                    <td colspan="5" class="px-4 py-2 text-right">Saldo</td>
                    <td class="px-4 py-2 text-right">Bs {{ number_format($saldo, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-semibold mb-3">Pagos registrados</h2>

        @if ($venta->pagos->isEmpty())
        <p class="text-sm text-gray-500 mb-3">Sin pagos registrados aún.</p>
        @else
        <table class="min-w-full text-sm mb-4">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">Fecha</th>
                    <th class="px-3 py-2 text-left">Forma</th>
                    <th class="px-3 py-2 text-left">Referencia</th>
                    <th class="px-3 py-2 text-right">Monto Bs</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venta->pagos as $p)
                <tr class="border-t">
                    <td class="px-3 py-2">{{ $p->fecha_hora?->format('d/m/Y H:i') }}</td>
                    <td class="px-3 py-2">{{ $p->forma_pago }}</td>
                    <td class="px-3 py-2">{{ $p->referencia ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format((float) $p->monto_bs, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if ($venta->estado !== 'CANCELADA')
        <form method="POST" action="{{ route('ventas.pagos.store', $venta) }}"
            class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-2 border-t">
            @csrf
            <select name="forma_pago" required class="border rounded px-3 py-2">
                <option value="">— Forma —</option>
                @foreach ($formas as $f)<option value="{{ $f }}">{{ $f }}</option>@endforeach
            </select>
            <input type="number" name="monto_bs" step="0.01" min="0.01" required
                placeholder="Monto Bs" class="border rounded px-3 py-2">
            <input type="text" name="referencia" maxlength="60"
                placeholder="Referencia (opcional)" class="border rounded px-3 py-2">
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Registrar pago</button>
        </form>
        @endif
    </div>
</div>

@if ($puedeCancelar)
<dialog id="dlg-cancelar" class="rounded-lg shadow-xl p-6 w-full max-w-md">
    <form method="POST" action="{{ route('ventas.cancelar', $venta) }}">
        @csrf
        <h2 class="text-lg font-semibold mb-2">Cancelar venta {{ $venta->numero_factura }}</h2>
        <p class="text-sm text-gray-600 mb-3">
            El stock saldrá de vuelta al almacén automáticamente vía movimiento ENTRADA.
            Esta acción no se puede revertir.
        </p>
        <label class="block text-sm font-medium">Motivo *</label>
        <textarea name="motivo" rows="3" minlength="5" maxlength="200" required
            class="w-full border rounded px-3 py-2 mb-3"></textarea>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()"
                class="border px-4 py-2 rounded">Cancelar</button>
            <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                Confirmar cancelación
            </button>
        </div>
    </form>
</dialog>
@endif
@endsection