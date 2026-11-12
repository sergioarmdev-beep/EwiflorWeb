{{--
    Campo para subir varias imágenes con vista previa.
    Variables: $etiqueta (texto del label)
--}}
<div>
    <label for="imagenes" class="block text-sm font-medium text-slate-300 mb-2">{{ $etiqueta }}</label>

    <div class="rounded-xl border border-dashed border-slate-700 bg-slate-950/50 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">

            <div class="w-12 h-12 shrink-0 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-sky-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                        d="M12 16V4m0 0L8 8m4-4l4 4M4 16v3a1 1 0 001 1h14a1 1 0 001-1v-3" />
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <input type="file" name="imagenes[]" id="imagenes" accept="image/jpeg,image/png,image/webp" multiple
                    class="block w-full text-sm text-slate-400
                    file:mr-4 file:py-2.5 file:px-4
                    file:rounded-lg file:border-0
                    file:text-sm file:font-semibold
                    file:bg-sky-500/10 file:text-sky-400
                    hover:file:bg-sky-500/20
                    file:cursor-pointer cursor-pointer">

                <p class="text-xs text-slate-500 mt-3">
                    Puede seleccionar varias a la vez (máx. 10). JPG, PNG o WEBP, hasta 2 MB cada una.
                </p>
            </div>
        </div>

        <div id="imagenes-preview" class="hidden mt-5 grid grid-cols-3 sm:grid-cols-5 gap-3"></div>
    </div>

    @error('imagenes')
    <span class="form-error">{{ $message }}</span>
    @enderror
    @foreach($errors->get('imagenes.*') as $mensajes)
    @foreach($mensajes as $mensaje)
    <span class="form-error">{{ $mensaje }}</span>
    @endforeach
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('imagenes');
        const preview = document.getElementById('imagenes-preview');
        if (!input || !preview) return;

        input.addEventListener('change', () => {
            preview.replaceChildren();
            const archivos = Array.from(input.files);
            preview.classList.toggle('hidden', archivos.length === 0);

            archivos.forEach((archivo, i) => {
                const caja = document.createElement('div');
                caja.className = 'relative aspect-square overflow-hidden rounded-lg border border-slate-700 bg-slate-900';

                const img = document.createElement('img');
                img.className = 'absolute inset-0 w-full h-full object-cover';
                img.alt = archivo.name;
                img.src = URL.createObjectURL(archivo);
                img.onload = () => URL.revokeObjectURL(img.src);
                caja.appendChild(img);

                if (i === 0 && {{ $esNuevoProducto ?? 'false' }}) {
                    const tag = document.createElement('span');
                    tag.className = 'absolute left-1.5 top-1.5 px-2 py-0.5 rounded-full bg-sky-500/90 text-white text-[10px] font-bold';
                    tag.textContent = 'Principal';
                    caja.appendChild(tag);
                }

                preview.appendChild(caja);
            });
        });
    });
</script>
