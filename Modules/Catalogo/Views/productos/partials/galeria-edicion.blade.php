{{-- Imágenes actuales del producto: elegir principal y marcar para eliminar. Variable: $producto --}}
@if($producto->imagenes->isNotEmpty())
<div class="mb-6">
    <div class="flex items-center justify-between gap-3 mb-3">
        <p class="text-sm font-medium text-muted">
            Imágenes actuales
            <span class="ml-1 text-xs text-muted">({{ $producto->imagenes->count() }})</span>
        </p>
        <p class="text-xs text-muted">Elija la principal o marque las que desea quitar.</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($producto->imagenes as $imagen)
        <div data-imagen-card
            class="group rounded-xl border border-line bg-surface-2 p-3 transition duration-200 has-[:checked]:border-brand-blue/40">

            <div class="relative w-full aspect-square overflow-hidden rounded-lg border border-line bg-surface">
                <img src="{{ $imagen->url }}" alt="{{ $imagen->texto_alternativo ?: $producto->nombre }}"
                    class="absolute inset-0 block w-full h-full object-contain transition duration-200" loading="lazy">

                @if($imagen->es_principal)
                <span class="absolute left-2 top-2 px-2 py-0.5 rounded-full bg-brand-green/90 text-white text-[10px] font-bold">
                    Principal
                </span>
                @endif
            </div>

            <div class="mt-3 space-y-2">
                <label class="flex items-center gap-2 text-xs text-muted cursor-pointer">
                    <input type="radio" name="imagen_principal" value="{{ $imagen->id_imagen }}"
                        {{ (string) old('imagen_principal', $producto->imagenes->firstWhere('es_principal', true)?->id_imagen) === (string) $imagen->id_imagen ? 'checked' : '' }}
                        class="w-4 h-4 border border-line bg-surface-2 text-brand-blue focus:ring-brand-blue/50 focus:ring-offset-surface">
                    Usar como principal
                </label>

                <label class="flex items-center gap-2 text-xs text-red-500 cursor-pointer">
                    <input type="checkbox" name="eliminar_imagenes[]" value="{{ $imagen->id_imagen }}"
                        {{ in_array($imagen->id_imagen, array_map('intval', old('eliminar_imagenes', []))) ? 'checked' : '' }}
                        data-imagen-eliminar
                        class="w-4 h-4 rounded border border-line bg-surface-2 text-red-500 focus:ring-red-500 focus:ring-offset-surface">
                    Eliminar al guardar
                </label>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-imagen-eliminar]').forEach((check) => {
            const aplicar = () => {
                const card = check.closest('[data-imagen-card]');
                const radio = card.querySelector('input[type="radio"]');
                card.querySelector('img').classList.toggle('opacity-30', check.checked);
                card.querySelector('img').classList.toggle('grayscale', check.checked);
                radio.disabled = check.checked;
                if (check.checked && radio.checked) radio.checked = false;
            };
            check.addEventListener('change', aplicar);
            aplicar();
        });
    });
</script>
@else
<div class="mb-6 rounded-xl border border-dashed border-line bg-surface-2/60 px-6 py-6 text-center text-xs text-muted">
    Este producto todavía no tiene imágenes.
</div>
@endif
