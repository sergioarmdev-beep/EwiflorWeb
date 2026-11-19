<!DOCTYPE html>
<html lang="es" class="h-full" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Servicio Técnico') | EwiflorWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Aplica el tema antes de pintar para evitar flash --}}
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.dataset.theme = saved || (systemDark ? 'dark' : 'light');
        })();
    </script>

    <style>
        html, body {
            background: var(--bg);
            color: var(--text);
        }
        .st-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.5rem;
        }
        .st-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
        }
    </style>
</head>

<body class="h-full overflow-hidden bg-canvas text-ink antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-line bg-nav-bg/90 backdrop-blur px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg bg-brand-blue flex items-center justify-center text-white font-bold">
                    ST
                </div>
                <div>
                    <span class="block text-xs font-semibold text-brand-blue uppercase tracking-wider">EwiflorWeb</span>
                    <span class="block text-sm font-semibold text-ink">Servicio Técnico — Cliente</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('serviciotecnico.publico.salir') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-muted hover:text-ink transition">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 px-6 py-8 max-w-5xl mx-auto">
            @if (session('success'))
                <div class="mb-4 flex items-start gap-3 rounded-xl border border-brand-green/30 bg-brand-green/10 px-4 py-3 text-sm text-brand-green" role="status">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-500" role="alert">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="border-t border-line bg-surface px-6 py-4 text-center text-[10px] text-muted">
            EwiflorWeb · Servicio Técnico — Panel público de cliente
        </footer>
    </div>
</body>
</html>
