{{--
    Campos «Nueva contraseña» + «Confirmar» con medidor de fortaleza y generador.
    Usa las variables de tema de resources/css/app.css (data-theme), sin clases dark:.
--}}
@php
$requerido = $requerido ?? true;
$etiqueta = $etiqueta ?? 'Contraseña';

$campo = 'mt-1 w-full px-3.5 py-2.5 pr-12 rounded-xl bg-surface-2 border border-line text-ink placeholder:text-muted text-sm focus:border-brand-green/50 focus:ring-brand-green/20';
$ojoBtn = 'absolute inset-y-0 right-0 pr-3.5 flex items-center text-muted hover:text-ink focus:outline-none transition-colors cursor-pointer';
@endphp

<div class="md:col-span-2 space-y-4" data-pw-root>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Nueva contraseña --}}
        <div>
            <label for="pw-new" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">
                {{ $etiqueta }} {{ $requerido ? '*' : '(dejar vacío para no cambiar)' }}
            </label>
            <div class="relative">
                <input type="password" name="password" id="pw-new" autocomplete="new-password"
                    placeholder="••••••••" minlength="8" maxlength="72" {{ $requerido ? 'required' : '' }}
                    class="{{ $campo }}">
                <button type="button" onclick="toggleFieldVisibility('pw-new', 'eye-open-1', 'eye-closed-1')"
                    class="{{ $ojoBtn }}" title="Ver contraseña">
                    <svg id="eye-open-1" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eye-closed-1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Confirmar contraseña --}}
        <div>
            <label for="pw-confirm" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Confirmar contraseña</label>
            <div class="relative">
                <input type="password" name="password_confirmation" id="pw-confirm" autocomplete="new-password"
                    placeholder="••••••••" minlength="8" maxlength="72" {{ $requerido ? 'required' : '' }}
                    class="{{ $campo }}">
                <button type="button" onclick="toggleFieldVisibility('pw-confirm', 'eye-open-2', 'eye-closed-2')"
                    class="{{ $ojoBtn }}" title="Ver contraseña">
                    <svg id="eye-open-2" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eye-closed-2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <p id="pw-match" class="text-xs mt-1 min-h-[1rem]" aria-live="polite"></p>
        </div>
    </div>

    {{-- Medidor de fortaleza + requisitos --}}
    <div class="p-3.5 rounded-xl border border-line bg-surface-2/60 space-y-3">
        <div class="flex items-center gap-3">
            <div class="flex-1 grid grid-cols-4 gap-1.5" role="progressbar" aria-label="Fortaleza de la contraseña"
                aria-valuemin="0" aria-valuemax="4" aria-valuenow="0" id="pw-meter">
                <span class="h-1.5 rounded-full border border-line bg-surface transition-colors duration-200" data-bar></span>
                <span class="h-1.5 rounded-full border border-line bg-surface transition-colors duration-200" data-bar></span>
                <span class="h-1.5 rounded-full border border-line bg-surface transition-colors duration-200" data-bar></span>
                <span class="h-1.5 rounded-full border border-line bg-surface transition-colors duration-200" data-bar></span>
            </div>
            <span id="pw-label" class="w-20 text-right text-[11px] font-semibold uppercase tracking-wider text-muted" aria-live="polite">—</span>
        </div>

        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-xs text-muted">
            <li data-req="len" class="flex items-center gap-1.5"><span data-dot class="w-1.5 h-1.5 rounded-full border border-line bg-surface"></span>Mínimo 8 caracteres</li>
            <li data-req="letra" class="flex items-center gap-1.5"><span data-dot class="w-1.5 h-1.5 rounded-full border border-line bg-surface"></span>Al menos una letra</li>
            <li data-req="num" class="flex items-center gap-1.5"><span data-dot class="w-1.5 h-1.5 rounded-full border border-line bg-surface"></span>Al menos un número</li>
            <li data-req="extra" class="flex items-center gap-1.5"><span data-dot class="w-1.5 h-1.5 rounded-full border border-line bg-surface"></span>Recomendado: mayúsculas, minúsculas y símbolos</li>
        </ul>

        {{-- Generador --}}
        <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-line">
            <button type="button" id="pw-generate"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-line bg-surface text-ink text-xs font-semibold hover:bg-surface-2 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-brand-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Generar contraseña segura
            </button>
            <button type="button" id="pw-copy"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-line bg-surface text-ink text-xs font-semibold hover:bg-surface-2 transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                <svg class="w-3.5 h-3.5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span id="pw-copy-text">Copiar</span>
            </button>
            <span class="text-[11px] text-muted">Se rellenan ambos campos. Cópiala antes de guardar.</span>
        </div>
    </div>
</div>

@once
<script>
    (function() {
        'use strict';

        // ── Mostrar / ocultar contraseña ───────────────────────
        function setVisible(inputId, openId, closedId, visible) {
            const input = document.getElementById(inputId);
            const open = document.getElementById(openId);
            const closed = document.getElementById(closedId);
            if (!input) return;
            input.type = visible ? 'text' : 'password';
            if (open) open.classList.toggle('hidden', !visible);
            if (closed) closed.classList.toggle('hidden', visible);
        }
        window.toggleFieldVisibility = function(inputId, openId, closedId) {
            const input = document.getElementById(inputId);
            setVisible(inputId, openId, closedId, input && input.type === 'password');
        };

        // ── Evaluación de fortaleza ────────────────────────────
        function evaluar(p) {
            const len = p.length >= 8;
            const letra = /[A-Za-zÀ-ɏ]/.test(p);
            const num = /\d/.test(p);
            const mix = /[a-zß-ÿ]/.test(p) && /[A-ZÀ-Þ]/.test(p);
            const sim = /[^A-Za-z0-9À-ɏ]/.test(p);
            const minimos = len && letra && num;
            let nivel = 0;
            if (p.length > 0) {
                nivel = 1;
                if (minimos) {
                    nivel = 2;
                    if (p.length >= 12 && mix) nivel = 3;
                    if (p.length >= 12 && mix && sim) nivel = 4;
                }
            }
            return {
                len,
                letra,
                num,
                extra: mix && sim,
                minimos,
                nivel
            };
        }

        // ── Paleta theme-aware (clases estáticas que respetan CSS vars) ────
        const BAR_ESTADOS = [
            [], // 0 vacío (sin color de fondo, solo borde)
            ['bg-red-500'], // 1 débil
            ['bg-amber-500'], // 2 aceptable
            ['bg-brand-blue'], // 3 buena
            ['bg-brand-green'], // 4 fuerte
        ];
        const TEXT_ESTADOS = [
            'text-muted', // 0
            'text-red-500', // 1
            'text-amber-500', // 2
            'text-brand-blue', // 3
            'text-brand-green', // 4
        ];
        const TXT_LABEL = ['—', 'Débil', 'Aceptable', 'Buena', 'Fuerte'];

        // ── Generador (CSPRNG, sin caracteres ambiguos) ────────
        function aleatorio(max) {
            const limite = Math.floor(0x100000000 / max) * max;
            const buf = new Uint32Array(1);
            do {
                crypto.getRandomValues(buf);
            } while (buf[0] >= limite);
            return buf[0] % max;
        }

        function generar(largo) {
            const grupos = ['abcdefghjkmnpqrstuvwxyz', 'ABCDEFGHJKMNPQRSTUVWXYZ', '23456789', '!@#$%*-_+?'];
            const todos = grupos.join('');
            const chars = grupos.map(g => g[aleatorio(g.length)]);
            while (chars.length < largo) chars.push(todos[aleatorio(todos.length)]);
            for (let i = chars.length - 1; i > 0; i--) {
                const j = aleatorio(i + 1);
                [chars[i], chars[j]] = [chars[j], chars[i]];
            }
            return chars.join('');
        }
        window.__pwTools = {
            evaluar,
            generar
        };

        // ── Bind ───────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            const nuevo = document.getElementById('pw-new');
            const conf = document.getElementById('pw-confirm');
            if (!nuevo || !conf) return;

            const barras = document.querySelectorAll('#pw-meter [data-bar]');
            const meter = document.getElementById('pw-meter');
            const etiqueta = document.getElementById('pw-label');
            const match = document.getElementById('pw-match');
            const btnCopiar = document.getElementById('pw-copy');
            const txtCopiar = document.getElementById('pw-copy-text');

            function pintarRequisito(clave, ok) {
                const li = document.querySelector('[data-req="' + clave + '"]');
                if (!li) return;
                li.classList.toggle('text-brand-green', ok);
                li.classList.toggle('text-muted', !ok);
                const dot = li.querySelector('[data-dot]');
                dot.classList.toggle('bg-brand-green', ok);
                dot.classList.toggle('bg-surface', !ok);
            }

            function actualizar() {
                const r = evaluar(nuevo.value);

                // Barras
                barras.forEach(function(b, i) {
                    b.className = 'h-1.5 rounded-full border border-line bg-surface transition-colors duration-200';
                    if (i < r.nivel) {
                        b.classList.add(BAR_ESTADOS[r.nivel][0]);
                    }
                });

                // Etiqueta
                etiqueta.className = 'w-20 text-right text-[11px] font-semibold uppercase tracking-wider transition-colors ' + TEXT_ESTADOS[r.nivel];
                etiqueta.textContent = TXT_LABEL[r.nivel];
                meter.setAttribute('aria-valuenow', r.nivel);

                // Requisitos
                ['len', 'letra', 'num', 'extra'].forEach(k => pintarRequisito(k, r[k]));

                btnCopiar.disabled = nuevo.value.length === 0;

                // Coincidencia
                if (conf.value.length === 0) {
                    match.textContent = '';
                    match.className = 'text-xs mt-1 min-h-[1rem] text-muted';
                } else if (conf.value === nuevo.value) {
                    match.textContent = 'Las contraseñas coinciden';
                    match.className = 'text-xs mt-1 min-h-[1rem] text-brand-green';
                } else {
                    match.textContent = 'Las contraseñas no coinciden';
                    match.className = 'text-xs mt-1 min-h-[1rem] text-red-500';
                }
            }

            nuevo.addEventListener('input', actualizar);
            conf.addEventListener('input', actualizar);

            document.getElementById('pw-generate').addEventListener('click', function() {
                const clave = generar(14);
                nuevo.value = clave;
                conf.value = clave;
                setVisible('pw-new', 'eye-open-1', 'eye-closed-1', true);
                setVisible('pw-confirm', 'eye-open-2', 'eye-closed-2', true);
                actualizar();
            });

            btnCopiar.addEventListener('click', async function() {
                const valor = nuevo.value;
                if (!valor) return;
                let ok = false;
                try {
                    await navigator.clipboard.writeText(valor);
                    ok = true;
                } catch (e) {
                    const prev = nuevo.type;
                    nuevo.type = 'text';
                    nuevo.select();
                    try {
                        ok = document.execCommand('copy');
                    } catch (e2) {
                        ok = false;
                    }
                    nuevo.type = prev;
                    nuevo.setSelectionRange(0, 0);
                }
                txtCopiar.textContent = ok ? '¡Copiada!' : 'No se pudo copiar';
                setTimeout(function() {
                    txtCopiar.textContent = 'Copiar';
                }, 1800);
            });

            actualizar();
        });
    })();
</script>
@endonce
