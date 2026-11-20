@extends('layouts.app')

@section('title', 'Olvidé mi contraseña')

@section('content')
<div class="max-w-md mx-auto px-4 py-6 text-ink">

    {{-- Encabezado --}}
    <div class="mb-8 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-blue text-white shadow-sm mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">¿Olvidaste tu contraseña?</h1>
        <p class="text-sm text-muted mt-1">
            Indica el correo y el celular con los que te registraste. Si coinciden, avisaremos a quien
            corresponde para que te asigne una clave temporal. No se revela si la cuenta existe.
        </p>
    </div>

    {{-- Éxito (mensaje idéntico con o sin coincidencia) --}}
    @if (session('recuperacion_ok'))
    <div class="mb-6 p-3.5 rounded-xl border border-brand-green/30 bg-brand-green/10 text-sm animate-[fade-in_0.2s_ease-in]" role="status">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-brand-green shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-brand-green">Listo</span>
                <p class="mt-0.5 text-xs text-brand-green/90">{{ session('recuperacion_ok') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Formulario de recuperación --}}
    <form method="POST" action="{{ route('password.olvide.store') }}" class="bg-surface border border-line rounded-2xl p-6 shadow-soft space-y-5">
        @csrf

        {{-- Honeypot anti-bots --}}
        <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
            <label>Sitio web <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label>
        </div>

        {{-- Correo --}}
        <div>
            <label for="correo" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Correo electrónico *</label>
            <input type="email" name="correo" id="correo" value="{{ old('correo') }}" required autofocus
                placeholder="correo@empresa.com" maxlength="100"
                class="mt-1 w-full px-3.5 py-2.5 rounded-xl bg-surface-2 border border-line text-ink placeholder:text-muted text-sm focus:border-brand-green/50 focus:ring-brand-green/20">
            @error('correo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Celular --}}
        <div>
            <label for="celular" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Número de celular *</label>
            <input type="tel" name="celular" id="celular" value="{{ old('celular') }}" required
                placeholder="+591 70000000" maxlength="20"
                class="mt-1 w-full px-3.5 py-2.5 rounded-xl bg-surface-2 border border-line text-ink placeholder:text-muted text-sm focus:border-brand-green/50 focus:ring-brand-green/20">
            @error('celular') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            <p class="text-[10px] text-muted mt-1">Debe coincidir con el celular registrado en tu cuenta.</p>
        </div>

        {{-- Envío --}}
        <div class="pt-2">
            <button type="submit"
                class="w-full py-3 px-4 bg-brand-green text-white font-semibold text-sm rounded-xl shadow-sm hover:bg-brand-green-dark transition-all duration-150 active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>Solicitar restablecimiento</span>
            </button>
        </div>

        {{-- Volver al login --}}
        <div class="text-center">
            <a href="{{ route('login') }}"
                class="text-xs font-medium text-muted hover:text-brand-green transition-colors duration-150">
                ← Volver al ingreso
            </a>
        </div>
    </form>
</div>
@endsection
