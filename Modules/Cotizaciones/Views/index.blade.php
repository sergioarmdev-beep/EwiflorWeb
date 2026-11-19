@extends('layouts.app')
@section('title', 'Cotizaciones')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-ink">Cotizaciones</h1>
        <a href="{{ route('cotizaciones.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-green text-white font-semibold hover:bg-brand-green-dark transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva cotización
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

    <form method="GET" class="bg-surface p-4 rounded-2xl border border-line shadow-soft grid grid-cols-1 md:grid-cols-5 gap-3">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Cliente o teléfono" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 placeholder:text-muted focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
        <select name="estado" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
            <option value="">— Estado —</option>
            @foreach (['EMITIDA','CONVERTIDA','ANULADA'] as $e)
            <option value="{{ $e }}" @selected(request('estado')===$e)>{{ $e }}</option>
            @endforeach
        </select>
        <select name="condicion" class="border border-line rounded-xl bg-surface-2 text-ink px-3 py-2 focus:border-brand-green/50 focus:ring-2 focus:ring-brand-green/20">
            <option value="">— Condición —</option>
            <option value="VIGENTE" @selected(request('condicion')==='VIGENTE' )>Vigente</option>
            <option value="VENCIDA" @selected(request('condicion')==='VENCIDA' )>Vencida</option>
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
            <a href="{{ route('cotizaciones.index') }}" class="border border-line rounded-xl px-4 py-2 text-ink hover:bg-surface-2 transition-colors">Limpiar</a>
        </div>
    </form>

    <div class="bg-surface rounded-2xl border border-line shadow-soft overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-line text-sm text-left">
            <thead class="bg-surface-2">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">#</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Fecha</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Cliente</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Vendedor</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Sucursal</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted">Total USD</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Estado</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted">Condición</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($cotizaciones as $c)
                <tr>
                    <td class="px-4 py-2 text-ink">{{ $c->id_cotizacion }}</td>
                    <td class="px-4 py-2 text-ink">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                    <td class="px-4 py-2 text-ink">{{ $c->cliente?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2 text-ink">{{ $c->usuario?->nombre_completo ?? '—' }}</td>
                    <td class="px-4 py-2 text-ink">{{ $c->sucursal?->nombre ?? '—' }}</td>
                    <td class="px-4 py-2 text-right text-ink">$ {{ number_format($c->totalUsd(), 2) }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                                {{ $c->estado === 'EMITIDA' ? 'border border-brand-blue/20 bg-brand-blue/10 text-brand-blue' : '' }}
                                {{ $c->estado === 'CONVERTIDA' ? 'border border-brand-green/20 bg-brand-green/10 text-brand-green' : '' }}
                                {{ $c->estado === 'ANULADA' ? 'border border-red-500/30 bg-red-500/10 text-red-500' : '' }}">
                            {{ $c->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        @php $cond = $c->condicionTemporal(); @endphp
                        <span class="text-xs
                                {{ $cond === 'VIGENTE' ? 'text-brand-green' : '' }}
                                {{ $cond === 'VENCIDA' ? 'text-red-400 font-semibold' : '' }}
                                {{ $cond === 'CERRADA' ? 'text-muted' : '' }}">
                            {{ $cond }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">
                        <a href="{{ route('cotizaciones.show', $c) }}" class="text-brand-blue hover:text-brand-blue-dark font-semibold">Ver</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-6 text-center text-muted">Sin cotizaciones.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $cotizaciones->links() }}</div>
</div>
@endsection
