<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo Ewiflor Importaciones</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Navbar Público -->
    <nav class="bg-blue-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold tracking-wider">Ewiflor Importaciones</h1>
            <a href="{{ route('login') }}" class="bg-blue-700 hover:bg-blue-600 px-4 py-2 rounded text-sm font-semibold transition">
                Acceso Empleados
            </a>
        </div>
    </nav>

    <!-- Contenido -->
    <main class="max-w-7xl mx-auto px-4 py-10">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-extrabold text-gray-900">Nuestro Catálogo de Maquinaria</h2>
            <p class="text-gray-500 mt-2">Equipos para panadería, repostería y carnicería de la más alta calidad.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($productos as $producto)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                <!-- Contenedor Dinámico de Imagen -->
                <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                    @if($producto->imagenPrincipal)
                    <img src="{{ asset('storage/' . $producto->imagenPrincipal->ruta_imagen) }}" class="object-cover w-full h-full">
                    @else
                    <span class="text-gray-400 text-sm">Sin imagen</span>
                    @endif
                </div>

                <div class="p-4">
                    <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">{{ $producto->marca->nombre ?? 'Genérico' }}</span>
                    <h3 class="text-lg font-bold text-gray-800 mt-1 truncate">{{ $producto->nombre }}</h3>
                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $producto->descripcion ?? 'Sin descripción disponible.' }}</p>

                    <div class="mt-4 flex justify-between items-center border-t pt-3">
                        <span class="text-xs text-gray-500">Garantía: {{ $producto->garantia_meses }} meses</span>
                        <span class="text-sm font-bold text-gray-700">Consultar Precio</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center text-gray-500 py-10">
                No hay productos disponibles en el catálogo público por el momento.
            </div>
            @endforelse
        </div>
    </main>
</body>

</html>