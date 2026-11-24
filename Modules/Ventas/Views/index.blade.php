@extends('layouts.app')
@section('title', 'Ventas')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-ink">Ventas</h1>
        <a href="{{ route('ventas.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-green text-white font-semibold hover:bg-brand-green-dark transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva venta
        </a>
    </div>

    @if (session('ok'))
    <div class="mb-3 flex items-start gap-3 rounded-xl border border-brand-green/30 bg-brand-green/10 px-4 py-2 text-sm text-brand-green" role="status">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
        </svg>
        <span>{{ session('ok') }}</span>
    </div>
    @endif
    @if (session('error'))
    <div class="mb-3 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm text-red-400" role="alert">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" d="M12 8v5m0 3h.01" />
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if ($aviso ?? null)
    <div class="mb-3 rounded-xl border border-brand-blue/30 bg-brand-blue/10 px-4 py-2 text-sm text-brand-blue">
        {{ $aviso }}
    </div>
    @endif

    @php
        $cant = (int) ($resumen->cantidad ?? 0);
        $totUsd = (float) ($resumen->total_usd ?? 0);
        $totBs = (float) ($resumen->total_bs ?? 0);
        $pagadoBs = (float) ($resumen->pagado_bs ?? 0);
        $saldoBs = max(0, $totBs - $pagadoBs);
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft text-sm">
            <p class="text-muted">Ventas del filtro</p>
            <p class="text-xl font-bold text-ink">{{ number_format($cant) }}</p>
        </div>
        <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft text-sm">
            <p class="text-muted">Total USD</p>
            <p class="text-xl font-bold text-ink">$ {{ number_format($totUsd, 2) }}</p>
        </div>
        <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft text-sm">
            <p class="text-muted">Total Bs</p>
            <p class="text-xl font-bold text-ink">Bs {{ number_format($totBs, 2) }}</p>
        </div>
        <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft text-sm">
            <p class="text-muted">Cobrado Bs</p>
            <p class="text-xl font-bold text-brand-green">Bs {{ number_format($pagadoBs, 2) }}</p>
        </div>
        <div class="bg-surface p-4 rounded-2xl border border-line shadow-soft text-sm">
            <p class="text-muted">Saldo Bs</p>
            <p class="text-xl font-bold {{ $saldoBs > 0 ? 'text-red-400' : 'text-brand-green' }}">
                Bs {{ number_format($saldoBs, 2) }}
            </p>
        </div>
    </div>

    <form method="GET" class="bg-surface p-4 rounded-2xl border border-line shadow-soft grid grid-cols-1 md:grid-cols-6 gap-3">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Factura o cliente" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 placeholder:text-muted focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
        <select name="estado" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
            <option value="">— Estado —</option>
            @foreach (['EN_PROCESO','REGISTRADA','CANCELADA'] as $e)
            <option value="{{ $e }}" @selected(request('estado')===$e)>{{ $e }}</option>
            @endforeach
        </select>
        <select name="cobro" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
            <option value="">— Cobro —</option>
            @foreach (['PAGADA','PARCIAL','PENDIENTE'] as $c)
            <option value="{{ $c }}" @selected(request('cobro')===$c)>
                {{ ['PAGADA' => 'Pagada', 'PARCIAL' => 'Pago parcial', 'PENDIENTE' => 'Sin pagar'][$c] }}
            </option>
            @endforeach
        </select>

        @if (($sucursales ?? collect())->isNotEmpty())
        <select name="id_sucursal" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
            <option value="">— Todas las sucursales —</option>
            @foreach ($sucursales as $s)
            <option value="{{ $s->id_sucursal }}" @selected((string) request('id_sucursal')===(string) $s->id_sucursal)>
                {{ $s->nombre }}
            </option>
            @endforeach
        </select>
        @endif

        @if (($usuarios ?? collect())->isNotEmpty())
        <select name="id_usuario" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
            <option value="">— Todos los vendedores —</option>
            @foreach ($usuarios as $u)
            <option value="{{ $u->id_usuario }}" @selected((string) request('id_usuario')===(string) $u->id_usuario)>
                {{ $u->nombre_completo }}
            </option>
            @endforeach
        </select>
        @endif

        <div class="flex gap-2">
            <button class="bg-brand-green text-white px-4 py-2 rounded-xl font-semibold hover:bg-brand-green-dark transition-colors">Filtrar</button>
            <a href="{{ route('ventas.index') }}" class="border border-line rounded-xl px-4 py-2 text-ink hover:bg-surface-2 transition-colors">Limpiar</a>
        </div>

        <div class="md:col-span-6 flex gap-3 items-center text-sm">
            <label class="flex items-center gap-1">
                Desde <input type="date" name="desde" value="{{ request('desde') }}" class="border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
            </label>
            <label class="flex items-center gap-1">
                Hasta <input type="date" name="hasta" value="{{ request('hasta') }}" class="border border-line rounded-xl bg-surface-2 text-ink px-2 py-1 focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
            </label>
        </div>
    </form>

    <div class="bg-surface rounded-2xl border border-line shadow-soft overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-line text-sm text-left">
            <thead class="bg-surface-2">
                <tr>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Factura</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Fecha</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Cliente</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Vendedor</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Sucursal</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Total USD</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Total Bs</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Cobrado Bs</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Estado</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($ventas as $v)
                <tr>
                    <td class="px-4 py-2 font-mono text-ink">{{ $v->numero_factura ?? '—' }}</td>
                    <td class="px-4 py-2 text-ink">{{ $v->fecha_hora?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2 text-ink">{{ $v->cliente?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2 text-ink">{{ $v->usuario?->nombre_completo ?? '—' }}</td>
                    <td class="px-4 py-2 text-ink">{{ $v->sucursal?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2 text-right text-ink">$ {{ number_format($v->total_usd ?? 0, 2) }}</td>
                    <td class="px-4 py-2 text-right text-ink">Bs {{ number_format($v->total_bs ?? 0, 2) }}</td>
                    <td class="px-4 py-2 text-right text-ink">Bs {{ number_format($v->pagado_bs ?? 0, 2) }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                                {{ $v->estado === 'REGISTRADA' ? 'border border-brand-green/20 bg-brand-green/10 text-brand-green' : '' }}
                                {{ $v->estado === 'EN_PROCESO' ? 'border border-amber-500/30 bg-amber-500/10 text-amber-500' : '' }}
                                {{ $v->estado === 'CANCELADA' ? 'border border-red-500/30 bg-red-500/10 text-red-500' : '' }}">
                            {{ $v->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('ventas.show', $v) }}" class="text-brand-blue hover:text-brand-blue-dark font-semibold">Ver</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-6 text-center text-muted">Sin ventas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $ventas->links() }}</div>
</div>
@endsection
