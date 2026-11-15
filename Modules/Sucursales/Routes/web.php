<?php

use Illuminate\Support\Facades\Route;
use Modules\Sucursales\Controllers\AlmacenController;
use Modules\Sucursales\Controllers\SucursalController;

Route::middleware(['auth'])
    ->prefix('sucursales')
    ->name('sucursales.')
    ->group(function () {

        // Consulta: todos los roles autenticados (alcance filtrado por AccesoSucursal).
        Route::get('/', [SucursalController::class, 'index'])->name('index');

        // Gestión: solo Gerente y Administrador.
        // 'crear' se registra ANTES de '/{sucursal}' para que no lo capture el binding.
        Route::middleware(['role:GERENTE,ADMINISTRADOR'])->group(function () {
            Route::get('/crear', [SucursalController::class, 'create'])->name('create');
            Route::post('/', [SucursalController::class, 'store'])->name('store');

            Route::get('/{sucursal}/editar', [SucursalController::class, 'edit'])->name('edit')
                ->whereNumber('sucursal');
            Route::put('/{sucursal}', [SucursalController::class, 'update'])->name('update')
                ->whereNumber('sucursal');
            Route::post('/{sucursal}/estado', [SucursalController::class, 'estado'])->name('estado')
                ->whereNumber('sucursal');

            // Almacenes anidados a la sucursal.
            Route::get('/{sucursal}/almacenes/crear', [AlmacenController::class, 'create'])->name('almacenes.create')
                ->whereNumber('sucursal');
            Route::post('/{sucursal}/almacenes', [AlmacenController::class, 'store'])->name('almacenes.store')
                ->whereNumber('sucursal');

            Route::get('/almacenes/{almacen}/editar', [AlmacenController::class, 'edit'])->name('almacenes.edit')
                ->whereNumber('almacen');
            Route::put('/almacenes/{almacen}', [AlmacenController::class, 'update'])->name('almacenes.update')
                ->whereNumber('almacen');
            Route::post('/almacenes/{almacen}/estado', [AlmacenController::class, 'estado'])->name('almacenes.estado')
                ->whereNumber('almacen');
        });

        // Ver detalle (después de las rutas de gestión para no chocar).
        Route::get('/{sucursal}', [SucursalController::class, 'show'])->name('show')
            ->whereNumber('sucursal');
    });
