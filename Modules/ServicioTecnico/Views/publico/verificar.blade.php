@extends('serviciotecnico::publico.layout')

@section('title', 'Verificar código')

@section('content')
<div class="max-w-md mx-auto">
    <div class="st-panel mx-auto">
        <h2 class="text-xl font-bold text-ink mb-2">Verifica tu identidad</h2>
        <p class="text-sm text-muted mb-6">
            Enviamos un código de 6 dígitos a <strong class="text-ink">{{ $correo }}</strong>.
            Revisa tu bandeja de entrada y espam.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-500" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('serviciotecnico.publico.verificar.codigo') }}" class="space-y-4">
            @csrf

            <div>
                <label for="codigo" class="block text-xs font-semibold text-muted mb-1 uppercase tracking-wider">Código de verificación</label>
                <input id="codigo" type="text" name="codigo" value="{{ old('codigo') }}"
                    inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6"
                    class="w-full px-3 py-2 text-sm tracking-[0.4em] text-center border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                    placeholder="000000" required autofocus>
            </div>

            <button type="submit"
                class="w-full px-4 py-2 bg-brand-green text-white rounded-xl font-semibold hover:bg-brand-green-dark transition">
                Verificar y continuar
            </button>
        </form>

        <form method="POST" action="{{ route('serviciotecnico.publico.reenviar') }}" class="mt-4">
            @csrf
            <button type="submit" class="text-xs text-brand-blue hover:underline">
                ¿No recibiste el código? Reenviarlo
            </button>
        </form>

        <p class="mt-4 text-center">
            <a href="{{ route('serviciotecnico.publico.identificar') }}" class="text-xs text-muted hover:text-ink hover:underline">
                Corregir mis datos
            </a>
        </p>
    </div>
</div>
@endsection
