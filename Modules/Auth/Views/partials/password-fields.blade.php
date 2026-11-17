{{--
    Campos «Nueva contraseña» + «Confirmar» con medidor de fortaleza y generador.
    Theme-aware: soporta claro (bg-white) y oscuro (bg-slate-800).
--}}
@php
$requerido = $requerido ?? true;
$etiqueta = $etiqueta ?? 'Contraseña';

$campo = 'mt-1 w-full px-3.5 py-2.5 pr-12 bg-white dark:bg-slate-800 border border-slate-300 dark:border-sky-500/40 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:border-[#0B6E45] dark:focus:border-sky-400 focus:ring-2 focus:ring-[#0B6E45]/30 dark:focus:ring-sky-500/30 transition-all';
$ojoBtn = 'absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none transition-colors cursor-pointer';
@endphp

<div class="md:col-span-2 space-y-4" data-pw-root>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Nueva contraseña --}}
        <div>
            <label for="pw-new" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
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
            @error('password') <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Confirmar contraseña --}}
        <div>
            <label for="pw-confirm" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">Confirmar contraseña</label>
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
    <div class="p-3.5 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl space-y-3">
        <div class="flex items-center gap-3">
            <div class="flex-1 grid grid-cols-4 gap-1.5" role="progressbar" aria-label="Fortaleza de la contraseña"
                aria-valuemin="0" aria-valuemax="4" aria-valuenow="0" id="pw-meter">
                <span class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 transition-colors duration-200" data-bar></span>
                <span class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 transition-colors duration-200" data-bar></span>
                <span class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 transition-colors duration-200" data-bar></span>
                <span class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 transition-colors duration-200" data-bar></span>
            </div>
            <span id="pw-label" class="w-20 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-500" aria-live="polite">—</span>
        </div>

        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-600 dark:text-slate-400">
            <li data-req="len" class="flex items-center gap-1.5"><span data-dot class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>Mínimo 8 caracteres</li>
            <li data-req="letra" class="flex items-center gap-1.5"><span data-dot class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>Al menos una letra</li>
            <li data-req="num" class="flex items-center gap-1.5"><span data-dot class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>Al menos un número</li>
            <li data-req="extra" class="flex items-center gap-1.5"><span data-dot class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>Recomendado: mayúsculas, minúsculas y símbolos</li>
        </ul>

        {{-- Generador --}}
        <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-slate-200 dark:border-slate-800">
            <button type="button" id="pw-generate"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#0B6E45] dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Generar contraseña segura
            </button>
            <button type="button" id="pw-copy"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span id="pw-copy-text">Copiar</span>
            </button>
            <span class="text-[11px] text-slate-500 dark:text-slate-500">Se rellenan ambos campos. Cópiala antes de guardar.</span>
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

        // ── Paleta theme-aware (funciona en claro y oscuro) ────
        const BAR_ESTADOS = [
            ['bg-slate-200', 'dark:bg-slate-800'], // 0 vacío
            ['bg-red-500'], // 1 débil
            ['bg-amber-500'], // 2 aceptable
            ['bg-sky-500'], // 3 buena
            ['bg-emerald-500'], // 4 fuerte
        ];
        const TEXT_ESTADOS = [
            'text-slate-500 dark:text-slate-500', // 0
            'text-red-500 dark:text-red-400', // 1
            'text-amber-600 dark:text-amber-300', // 2
            'text-sky-600 dark:text-sky-300', // 3
            'text-emerald-600 dark:text-emerald-300', // 4
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
                li.classList.toggle('text-emerald-600', ok);
                li.classList.toggle('dark:text-emerald-300', ok);
                li.classList.toggle('text-slate-600', !ok);
                li.classList.toggle('dark:text-slate-400', !ok);
                const dot = li.querySelector('[data-dot]');
                dot.classList.toggle('bg-emerald-500', ok);
                dot.classList.toggle('bg-slate-300', !ok);
                dot.classList.toggle('dark:bg-slate-600', !ok);
            }

            function actualizar() {
                const r = evaluar(nuevo.value);

                // Barras
                barras.forEach(function(b, i) {
                    BAR_ESTADOS.flat().forEach(c => b.classList.remove(c));
                    const estado = (i < r.nivel) ? BAR_ESTADOS[r.nivel] : BAR_ESTADOS[0];
                    estado.forEach(c => b.classList.add(c));
                });

                // Etiqueta
                TEXT_ESTADOS.forEach(c => c.split(' ').forEach(cls => etiqueta.classList.remove(cls)));
                TEXT_ESTADOS[r.nivel].split(' ').forEach(cls => etiqueta.classList.add(cls));
                etiqueta.textContent = TXT_LABEL[r.nivel];
                meter.setAttribute('aria-valuenow', r.nivel);

                // Requisitos
                ['len', 'letra', 'num', 'extra'].forEach(k => pintarRequisito(k, r[k]));

                btnCopiar.disabled = nuevo.value.length === 0;

                // Coincidencia
                if (conf.value.length === 0) {
                    match.textContent = '';
                    match.className = 'text-xs mt-1 min-h-[1rem]';
                } else if (conf.value === nuevo.value) {
                    match.textContent = 'Las contraseñas coinciden';
                    match.className = 'text-xs mt-1 min-h-[1rem] text-emerald-600 dark:text-emerald-400';
                } else {
                    match.textContent = 'Las contraseñas no coinciden';
                    match.className = 'text-xs mt-1 min-h-[1rem] text-red-500 dark:text-red-400';
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