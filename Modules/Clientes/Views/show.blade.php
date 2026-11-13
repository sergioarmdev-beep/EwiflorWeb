@extends('layouts.app')

@section('title', $cliente->nombre)

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl
                        bg-sky-100 text-sky-700
                        dark:bg-sky-500/10 dark:text-sky-400">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m12-13a4 4 0 11-8 0 4 4 0 018 0zm2 4h6m-3-3v6" />
                </svg>
            </div>

            <div>
                <h1 class="text-2xl font-bold tracking-tight
                           text-slate-800 dark:text-slate-100">
                    {{ $cliente->nombre }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Información y actividad del cliente.
                </p>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="flex flex-wrap items-center gap-2">

            @if (in_array(auth()->user()->nombre_rol, ['GERENTE','ADMINISTRADOR','VENDEDOR']))
            <a href="{{ route('clientes.edit', $cliente) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg
                          bg-sky-600 hover:bg-sky-700
                          text-white text-sm font-medium
                          transition-colors">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7.5 1.5L19 6a2.12 2.12 0 00-3-3l-9.5 9.5L6 16l3.5-.5z" />
                </svg>

                Editar
            </a>
            @endif

            @if (in_array(auth()->user()->nombre_rol, ['GERENTE','ADMINISTRADOR']))
            <form method="POST" action="{{ route('clientes.toggleEstado', $cliente) }}">
                @csrf
                @method('PATCH')

                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg
                                   border text-sm font-medium transition-colors
                                   {{ $cliente->estado === 'ACTIVO'
                                       ? 'border-red-200 text-red-600 hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10'
                                       : 'border-green-200 text-green-700 hover:bg-green-50 dark:border-green-500/30 dark:text-green-400 dark:hover:bg-green-500/10' }}">

                    @if ($cliente->estado === 'ACTIVO')
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" />
                    </svg>
                    Desactivar
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7" />
                    </svg>
                    Reactivar
                    @endif
                </button>
            </form>
            @endif

            <a href="{{ route('clientes.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg
                      border border-slate-300 dark:border-slate-700
                      text-sm font-medium text-slate-700 dark:text-slate-300
                      hover:bg-slate-100 dark:hover:bg-slate-800
                      transition-colors">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>

                Volver
            </a>
        </div>
    </div>

    {{-- Mensaje de confirmación --}}
    @if (session('ok'))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl
                    bg-green-50 border border-green-200 text-green-800
                    dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-400">

        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5 shrink-0" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7" />
        </svg>

        <p class="text-sm font-medium">
            {{ session('ok') }}
        </p>
    </div>
    @endif

    {{-- Información del cliente --}}
    <div class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-2xl shadow-sm overflow-hidden">

        {{-- Cabecera --}}
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800
                    flex items-center justify-between gap-3">

            <div>
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                    Información del cliente
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Datos generales y características del registro.
                </p>
            </div>

            {{-- Estado --}}
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
                         text-xs font-semibold
                         {{ $cliente->estado === 'ACTIVO'
                             ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400'
                             : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">

                <span class="w-1.5 h-1.5 rounded-full
                             {{ $cliente->estado === 'ACTIVO' ? 'bg-green-500' : 'bg-red-500' }}">
                </span>

                {{ $cliente->estado }}
            </span>
        </div>

        {{-- Datos --}}
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">

            {{-- Teléfono --}}
            <div>
                <p class="text-xs font-medium uppercase tracking-wider
                          text-slate-500 dark:text-slate-400 mb-2">
                    Teléfono
                </p>
                <div class="flex items-center gap-2 text-sm font-medium text-slate-800 dark:text-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 text-sky-600 dark:text-sky-400"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.1 3.3a1 1 0 01-.5 1.21l-2.12 1.06a11.04 11.04 0 005.29 5.29l1.06-2.12a1 1 0 011.21-.5l3.3 1.1a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.27 21 3 14.73 3 7V5z" />
                    </svg>
                    {{ $cliente->telefono }}
                </div>
            </div>

            {{-- Correo --}}
            <div>
                <p class="text-xs font-medium uppercase tracking-wider
                          text-slate-500 dark:text-slate-400 mb-2">
                    Correo electrónico
                </p>
                <div class="flex items-center gap-2 text-sm font-medium text-slate-800 dark:text-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 text-sky-600 dark:text-sky-400"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M3 8l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                    </svg>
                    {{ $cliente->correo ?? '—' }}
                </div>
            </div>

            {{-- Tipo --}}
            <div>
                <p class="text-xs font-medium uppercase tracking-wider
                          text-slate-500 dark:text-slate-400 mb-2">
                    Tipo de cliente
                </p>
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                    {{ $cliente->tipo_cliente ?? '—' }}
                </p>
            </div>

            {{-- Mayorista --}}
            <div>
                <p class="text-xs font-medium uppercase tracking-wider
                          text-slate-500 dark:text-slate-400 mb-2">
                    Clasificación comercial
                </p>
                <span class="inline-flex items-center gap-2 text-sm font-medium
                             text-slate-800 dark:text-slate-200">
                    <span class="w-2 h-2 rounded-full
                                 {{ $cliente->es_mayorista ? 'bg-sky-500' : 'bg-slate-400' }}">
                    </span>
                    {{ $cliente->es_mayorista ? 'Mayorista' : 'Minorista' }}
                </span>
            </div>

            {{-- Fecha de registro --}}
            <div class="sm:col-span-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <p class="text-xs font-medium uppercase tracking-wider
                          text-slate-500 dark:text-slate-400 mb-2">
                    Fecha de registro
                </p>
                <div class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 text-sky-600 dark:text-sky-400"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                    </svg>
                    {{ $cliente->fecha_registro?->format('d/m/Y H:i') ?? '—' }}
                </div>
            </div>

        </div>
    </div>

    {{-- Historial --}}
    <div class="bg-white dark:bg-slate-900
                border border-slate-200 dark:border-slate-800
                rounded-2xl shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-sky-600 dark:text-sky-400"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                    Historial del cliente
                </h2>
            </div>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Registro de las operaciones y actividades relacionadas.
            </p>
        </div>

        <div class="p-6">
            <div class="flex flex-col items-center justify-center text-center py-6">

                <div class="flex items-center justify-center w-12 h-12 rounded-full
                            bg-slate-100 text-slate-500
                            dark:bg-slate-800 dark:text-slate-400 mb-3">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-6 h-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Sin actividad registrada
                </h3>

                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-md">
                    Aquí aparecerán las cotizaciones, ventas y garantías
                    asociadas a este cliente cuando esos módulos estén implementados.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection