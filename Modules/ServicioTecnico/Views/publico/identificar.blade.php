@extends('serviciotecnico::publico.layout')

@section('title', 'Identificarse')

@section('content')
<div class="max-w-md mx-auto">
    <div class="st-panel mx-auto">
        <h2 class="text-xl font-bold text-white mb-6">Identificarse como cliente</h2>

        <form method="POST" action="{{ route('serviciotecnico.publico.validar') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Nombre completo</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}"
                    class="w-full px-3 py-2 text-sm border border-slate-600 rounded bg-slate-800 text-white focus:border-sky-500 focus:outline-none"
                    placeholder="Juan Pérez" required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Número de celular</label>
                <input type="tel" name="telefono" value="{{ old('telefono') }}"
                    class="w-full px-3 py-2 text-sm border border-slate-600 rounded bg-slate-800 text-white focus:border-sky-500 focus:outline-none"
                    placeholder="71234567" required>
            </div>

            <button type="submit"
                class="w-full px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded font-semibold transition">
                Acceder a mis compras y garantías
            </button>
        </form>

        <p class="mt-4 text-xs text-slate-500 text-center">
            Solo puedes consultar si tienes compras registradas en EwiflorWeb.
        </p>
    </div>
</div>
@endsection
