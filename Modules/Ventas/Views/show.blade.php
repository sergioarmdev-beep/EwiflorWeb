@extends('layouts.app')
@section('title', "Venta {$venta->numero_factura}")

@section('content')
@php
$rol = auth()->user()->nombre_rol;
$puedeCancelar = in_array($rol, ['GERENTE','ADMINISTRADOR']) && $venta->estado !== 'CANCELADA';
$saldo = $venta->saldoBs();
@endphp

<div class="p-6 max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink">
            Venta {{ $venta->numero_factura }}
            <span class="ml-2 text-xs px-2 py-1 rounded
                {{ $venta->estado === 'REGISTRADA' ? 'border border-brand-green/20 bg-brand-green/10 text-brand-green' : '' }}
                {{ $venta->estado === 'EN_PROCESO' ? 'border border-brand-blue/20 bg-brand-blue/10 text-brand-blue' : '' }}
                {{ $venta->estado === 'CANCELADA' ? 'border border-red-500/30 bg-red-500/10 text-red-500' : '' }}">
                {{ $venta->estado }}
            </span>
        </h1>
        <div class="flex gap-2">
            <a href="{{ route('ventas.comprobante', $venta) }}" target="_blank"
                class="border border-line rounded-xl px-4 py-2 text-ink hover:bg-surface-2 transition">Imprimir</a>
            @if ($puedeCancelar)
            <button type="button" onclick="document.getElementById('dlg-cancelar').showModal()"
                class="border border-red-500/30 bg-red-500/10 text-red-500 px-4 py-2 rounded-xl hover:bg-red-500/20 transition">Cancelar venta</button>
            @endif
            <a href="{{ route('ventas.index') }}" class="border border-line rounded-xl px-4 py-2 text-ink hover:bg-surface-2 transition">Volver</a>
        </div>
    </div>

    @if (session('ok'))
    <div class="mb-3 flex items-start gap-3 rounded-xl border border-brand-green/30 bg-brand-green/10 px-4 py-2 text-sm text-brand-green">
        {{ session('ok') }}
    </div>
    @endif
    @if (session('error'))
    <div class="mb-3 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-500">
        {{ session('error') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="mb-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-500">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="bg-surface p-6 rounded-2xl border border-line shadow-soft grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div><span class="text-muted">Cliente:</span> {{ $venta->cliente?->nombre ?? '—' }}</div>
        <div><span class="text-muted">Teléfono:</span> {{ $venta->cliente?->telefono ?? '—' }}</div>
        <div><span class="text-muted">Sucursal:</span> {{ $venta->sucursal?->nombre ?? '—' }}</div>
        <div><span class="text-muted">Vendedor:</span> {{ $venta->usuario?->nombre_completo ?? '—' }}</div>
        <div><span class="text-muted">Fecha:</span> {{ $venta->fecha_hora?->format('d/m/Y H:i') }}</div>
        <div><span class="text-muted">T/C aplicado:</span>
            {{ $venta->tipoCambio?->tipo }} — {{ number_format((float) $venta->tipoCambio?->valor, 4) }} Bs/USD
        </div>
        @if ($venta->estado === 'CANCELADA')
        <div class="md:col-span-3 p-3 rounded-xl border border-red-500/30 bg-red-500/20 text-red-500">
            Cancelada el {{ $venta->fecha_cancelacion?->format('d/m/Y H:i') }}
            por {{ $venta->usuarioCancela?->nombre_completo ?? '—' }}.
            Motivo: {{ $venta->motivo_cancelacion }}
        </div>
        @endif
    </div>

    <div class="bg-surface rounded-2xl border border-line shadow-soft overflow-hidden overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-surface-2">
                <tr>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">SKU</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Producto</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Cant.</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">P. Unit. USD</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Desc. %</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Garantía (meses)</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Subtotal USD</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($venta->detalles as $d)
                <tr>
                    <td class="px-4 py-2 text-ink">{{ $d->variante?->sku ?? '—' }}</td>
                    <td class="px-4 py-2 text-ink">{{ $d->variante?->producto?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2 text-right text-ink">{{ $d->cantidad }}</td>
                    <td class="px-4 py-2 text-right text-ink">{{ number_format((float) $d->precio_unitario_usd, 2) }}</td>
                    <td class="px-4 py-2 text-right text-ink">{{ number_format((float) $d->descuento_pct, 2) }}</td>
                    <td class="px-4 py-2 text-right text-ink">
                        @php
                            $gm = $d->garantia_meses;
                            if ($gm === null && $d->variante?->producto) {
                                $gm = $d->variante->producto->garantia_meses;
                            }
                        @endphp
                        {{ $gm ?? '—' }}
                        @if ($d->garantia && $d->garantia->duracion_meses)
                            <span class="text-xs text-brand-blue ml-1">(registrada: {{ $d->garantia->duracion_meses }}m)</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right text-ink">{{ number_format($d->subtotalUsd(), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-surface-2 border-t border-line font-semibold">
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right text-ink">Total USD</td>
                    <td class="px-4 py-2 text-right text-ink">$ {{ number_format($totales->total_usd ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right text-ink">Total Bs</td>
                    <td class="px-4 py-2 text-right text-ink">Bs {{ number_format($totales->total_bs ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="px-4 py-2 text-right text-ink">Pagado</td>
                    <td class="px-4 py-2 text-right text-brand-green">Bs {{ number_format($venta->totalPagadoBs(), 2) }}</td>
                </tr>
                <tr class="{{ $saldo > 0 ? 'text-red-500' : 'text-brand-green' }}">
                    <td colspan="5" class="px-4 py-2 text-right text-ink">Saldo</td>
                    <td class="px-4 py-2 text-right text-ink">Bs {{ number_format($saldo, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="bg-surface p-6 rounded-2xl border border-line shadow-soft">
        <h2 class="font-bold text-ink mb-3">Pagos registrados</h2>

        @if ($venta->pagos->isEmpty())
        <p class="text-sm text-muted mb-3">Sin pagos registrados aún.</p>
        @else
        <table class="min-w-full text-sm mb-4">
            <thead class="bg-surface-2">
                <tr>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Fecha</th>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Forma</th>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Referencia</th>
                    <th scope="col" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Monto Bs</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach ($venta->pagos as $p)
                <tr>
                    <td class="px-3 py-2 text-ink">{{ $p->fecha_hora?->format('d/m/Y H:i') }}</td>
                    <td class="px-3 py-2 text-ink">{{ $p->forma_pago }}</td>
                    <td class="px-3 py-2 text-ink">{{ $p->referencia ?? '—' }}</td>
                    <td class="px-3 py-2 text-right text-ink">{{ number_format((float) $p->monto_bs, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if ($puedePagar)
        <form method="POST" action="{{ route('ventas.pagos.store', $venta) }}"
            class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-2 border-t border-line">
            @csrf
            <p class="md:col-span-4 text-sm text-muted">
                Pago único. Puede ser parcial; después de registrarlo no se admiten más pagos.
            </p>
            <select name="forma_pago" required class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-brand-green/20">
                <option value="">— Forma —</option>
                @foreach ($formas as $f)
                <option value="{{ $f }}" @selected(old('forma_pago') === $f)>{{ $f }}</option>
                @endforeach
            </select>
            <input type="number" name="monto_bs" step="0.01" min="0.01"
                max="{{ number_format($totalBs, 2, '.', '') }}" required
                value="{{ old('monto_bs', number_format($totalBs, 2, '.', '')) }}"
                placeholder="Monto Bs (máx. {{ number_format($totalBs, 2, '.', '') }})"
                class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-brand-green/20">
            <input type="text" name="referencia" maxlength="60" value="{{ old('referencia') }}"
                placeholder="Referencia (opcional)" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-brand-green/20">
            <button class="bg-brand-green text-white px-4 py-2 rounded-xl font-semibold hover:bg-brand-green-dark transition-colors">Registrar pago único</button>
        </form>
        @elseif ($venta->pagos->isNotEmpty() && $venta->estado !== 'CANCELADA')
            @if ($saldo > 0)
            <div class="bg-amber-500/20 text-amber-500 px-4 py-2 rounded-xl text-sm border-t border-line">
                Pago parcial registrado. Esta venta no admite pagos adicionales.
                Saldo pendiente: <strong class="text-ink">Bs {{ number_format($saldo, 2) }}</strong>.
            </div>
            @else
            <div class="bg-brand-green/20 text-brand-green px-4 py-2 rounded-xl text-sm border-t border-line">
                Venta pagada en su totalidad. No se admiten más pagos.
            </div>
            @endif
        @elseif ($venta->estado !== 'CANCELADA')
        <p class="text-sm text-muted">Esta venta no tiene un monto pendiente de cobro.</p>
        @endif
    </div>
</div>

@if ($puedeCancelar)
<dialog id="dlg-cancelar" class="bg-surface rounded-2xl shadow-modal p-6 w-full max-w-md">
    <form method="POST" action="{{ route('ventas.cancelar', $venta) }}">
        @csrf
        <h2 class="text-lg font-bold text-ink mb-2">Cancelar venta {{ $venta->numero_factura }}</h2>
        <p class="text-sm text-muted mb-3">
            El stock saldrá de vuelta al almacén automáticamente vía movimiento ENTRADA.
            Esta acción no se puede revertir.
        </p>
        <label class="block text-sm font-semibold text-muted">Motivo *</label>
        <textarea name="motivo" rows="3" minlength="5" maxlength="200" required
            class="w-full border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 mb-3 focus:border-brand-green/50 focus:ring-brand-green/20"></textarea>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()"
                class="border border-line rounded-xl px-4 py-2 text-ink hover:bg-surface-2 transition">Cancelar</button>
            <button class="bg-red-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-red-400 transition-colors">
                Confirmar cancelación
            </button>
        </div>
    </form>
</dialog>
@endif
@endsection
