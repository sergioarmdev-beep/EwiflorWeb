@extends('serviciotecnico::publico.layout')

@section('title', 'Identificarse')

@section('content')
<div class="max-w-md mx-auto">
    <div class="st-panel mx-auto">
        <h2 class="text-xl font-bold text-ink mb-6">Identificarse como cliente</h2>

        <form method="POST" action="{{ route('serviciotecnico.publico.validar') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-muted mb-1 uppercase tracking-wider">Nombre completo</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}"
                    class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                    placeholder="Juan Pérez" required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted mb-1 uppercase tracking-wider">Número de celular</label>
                <input type="tel" name="telefono" value="{{ old('telefono') }}"
                    class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                    placeholder="71234567" required>
            </div>

            <button type="submit"
                class="w-full px-4 py-2 bg-brand-green text-white rounded-xl font-semibold hover:bg-brand-green-dark transition">
                Acceder a mis compras y garantías
            </button>
        </form>

        <p class="mt-4 text-xs text-muted text-center">
            Solo puedes consultar si tienes compras registradas en EwiflorWeb.
        </p>
    </div>
</div>
@endsection
