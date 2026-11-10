<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EwiflorWeb - Sistema de Gestión</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen bg-gray-200">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="h-16 flex items-center justify-center border-b border-gray-800">
                <span class="text-xl font-bold">Ewiflor</span>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Dashboard</a>
                <a href="{{ route('catalogo.productos.index') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Productos</a>

                <a href="#" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-500">Clientes (Próximamente)</a>
                <a href="#" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-500">Ventas (Próximamente)</a>
            </nav>
            <div class="p-4 border-t border-gray-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-red-400 hover:text-red-300">
                        Cerrar Sesión ({{ Auth::user()->nombre_usuario }})
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <header class="bg-white shadow-sm h-16 flex items-center px-6">
                <h1 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
            </header>
            <div class="p-6">
                @if(session('success'))
                <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                    {{ session('success') }}
                </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>