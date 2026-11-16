<!DOCTYPE html>
<html lang="es" class="h-full dark" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Servicio Técnico') | EwiflorWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body {
            background: #020617;
            color: #f1f5f9;
        }
        .st-panel {
            background: #0f172a;
            border: 1px solid #1e293b;
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
        }
        .st-badge-pending { background: #fef3c7; color: #92400e; }
        .st-badge-programada { background: #dbeafe; color: #1e40af; }
        .st-badge-cerrada { background: #d1fae5; color: #065f46; }
    </style>
</head>

<body class="h-full overflow-hidden bg-slate-950 text-slate-100 antialiased">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg bg-sky-600 flex items-center justify-center text-white font-bold">
                    ST
                </div>
                <div>
                    <span class="block text-xs font-semibold text-sky-300 uppercase tracking-wider">EwiflorWeb</span>
                    <span class="block text-sm font-semibold text-white">Servicio Técnico — Cliente</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('serviciotecnico.publico.salir') }}" method="POST" onclick="event.preventDefault(); this.closest('form').requestSubmit();" class="text-xs text-slate-400 hover:text-white transition">
                    Cerrar sesión
                </a>
                <form action="{{ route('serviciotecnico.publico.salir') }}" method="POST" style="display:none;">
                    @csrf
                    <button type="submit"></button>
                </form>
            </div>
        </header>

        <main class="flex-1 px-6 py-8 max-w-5xl mx-auto">
            @if (session('success'))
                <div class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/10 px-4 py-3 text-sm text-emerald-300 dark:border-emerald-500/30 dark:text-emerald-200" role="status">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50/10 px-4 py-3 text-sm text-red-300 dark:border-red-500/30 dark:text-red-200" role="alert">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="border-t border-slate-800 bg-slate-950 px-6 py-4 text-center text-[10px] text-slate-500">
            EwiflorWeb · Servicio Técnico — Panel público de cliente
        </footer>
    </div>

    <form id="salir-form" action="{{ route('serviciotecnico.publico.salir') }}" method="POST" style="display:none;">
        @csrf
    </form>
</body>
</html>
