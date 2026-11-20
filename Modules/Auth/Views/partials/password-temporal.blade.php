{{--
    Contraseña temporal generada (flash de sesión): se muestra UNA sola vez.
    Usado en Gestión de Usuarios y en la bandeja de solicitudes de clave.
--}}
@if (session('password_temporal'))
@php($tmp = session('password_temporal'))
<div class="mb-6 p-4 rounded-2xl border border-amber-500/30 bg-amber-500/20 shadow-soft">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
        </svg>
        <div class="min-w-0 flex-1">
            <span class="block text-xs font-semibold uppercase tracking-wider text-amber-500">Contraseña temporal generada</span>
            <p class="mt-1 text-xs text-amber-400/90">
                Para <strong class="text-ink">{{ $tmp['nombre'] }}</strong>
                (<span class="font-mono">{{ $tmp['nombre_usuario'] }}</span>).
                Compártela por un medio seguro: <strong>no se volverá a mostrar</strong>. Deberá cambiarla al iniciar sesión.
            </p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <code id="tmp-password" class="px-3 py-2 bg-surface-2 border border-line rounded-lg text-sm font-mono tracking-wider text-ink select-all">{{ $tmp['password'] }}</code>
                <button type="button" id="tmp-copy"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-brand-green text-white rounded-xl font-bold text-xs hover:bg-brand-green-dark transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span id="tmp-copy-text">Copiar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('tmp-copy')?.addEventListener('click', async function () {
        const el = document.getElementById('tmp-password');
        const txt = document.getElementById('tmp-copy-text');
        let ok = false;
        try {
            await navigator.clipboard.writeText(el.textContent.trim());
            ok = true;
        } catch (e) {
            const r = document.createRange();
            r.selectNodeContents(el);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(r);
            try { ok = document.execCommand('copy'); } catch (e2) { ok = false; }
        }
        txt.textContent = ok ? '¡Copiada!' : 'Selecciona y copia';
        setTimeout(() => { txt.textContent = 'Copiar'; }, 1800);
    });
</script>
@endif
