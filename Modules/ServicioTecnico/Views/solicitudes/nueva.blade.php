@extends('layouts.app')

@section('title', 'Nueva solicitud de servicio técnico')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ink">Nueva solicitud de servicio técnico</h1>
        <p class="text-sm text-muted">Crea una solicitud en nombre de un cliente (contacto por vendedor).</p>
    </div>

    <div class="bg-surface rounded-xl shadow-soft overflow-hidden">
        <div class="px-6 py-4 border-b border-line">
            <h2 class="text-lg font-bold text-ink">Cliente y falla</h2>
        </div>

        <form method="POST" action="{{ route('serviciotecnico.nueva.store') }}" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Nombre del cliente</label>
                    <input type="text" name="nombre_cliente" value="{{ old('nombre_cliente') }}"
                        class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                        placeholder="Juan Pérez" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Celular</label>
                    <input type="tel" name="telefono_cliente" value="{{ old('telefono_cliente') }}"
                        class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                        placeholder="71234567" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Descripción de la falla</label>
                <textarea name="descripcion_falla" rows="4"
                    class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20"
                    placeholder="Ej: El equipo no enciende..." required></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Garantía (opcional)</label>
                <select name="id_garantia"
                    class="w-full px-3 py-2 text-sm border border-line rounded-xl bg-surface-2 text-ink focus:border-brand-green/50 focus:ring-brand-green/20">
                    <option value="">Sin garantía (servicio pagado)</option>
                </select>
                <p class="text-xs text-muted mt-1">Déjalo vacío si el cliente no tiene garantía vigente.</p>
            </div>

            <div class="flex justify-end pt-4">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm border border-line rounded-xl text-ink hover:bg-surface-2">Cancelar</a>
                <button type="submit"
                    class="ml-2 px-4 py-2 bg-brand-green text-white rounded-xl font-semibold hover:bg-brand-green-dark transition">
                    Crear solicitud
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
