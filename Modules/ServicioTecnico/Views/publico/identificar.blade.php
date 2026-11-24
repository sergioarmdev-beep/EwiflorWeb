@extends('serviciotecnico::publico.layout')

@section('title', 'Identificarse')

@section('content')
<div class="max-w-md mx-auto">
    <div class="st-panel mx-auto">
        <h2 class="text-xl font-bold text-ink mb-2">Identificarse como cliente</h2>
        <p class="text-sm text-muted mb-6">
            Ingresa los datos con los que estás registrado. Te enviaremos un código de verificación a tu correo.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-500" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('serviciotecnico.publico.validar') }}" class="space-y-4">
            @csrf

            <div>
                <label for="nombre" class="block text-xs font-semibold text-muted mb-1 uppercase tracking-wider">Nombre completo</label>
                <input id="nombre" type="text" name="nombre" value="{{ old('nombre') }}"
                    class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                    placeholder="Juan Pérez" required>
            </div>

            <div>
                <label for="telefono" class="block text-xs font-semibold text-muted mb-1 uppercase tracking-wider">Número de celular</label>
                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono') }}"
                    class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                    placeholder="71234567" required>
            </div>

            <div>
                <label for="correo" class="block text-xs font-semibold text-muted mb-1 uppercase tracking-wider">Correo electrónico registrado</label>
                <input id="correo" type="email" name="correo" value="{{ old('correo') }}"
                    class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                    placeholder="nombre@ejemplo.com" required autocomplete="email">
            </div>

            <button type="submit"
                class="w-full px-4 py-2 bg-brand-green text-white rounded-xl font-semibold hover:bg-brand-green-dark transition">
                Enviar código de verificación
            </button>
        </form>

        <p class="mt-4 text-xs text-muted text-center">
            Solo puedes consultar si tienes compras registradas en EwiflorWeb y tu correo está registrado.
        </p>
    </div>
</div>
@endsection
