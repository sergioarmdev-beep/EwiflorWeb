<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\Controllers\ExistenciaController;
use Modules\Inventario\Controllers\MovimientoController;

Route::middleware(['auth'])
    ->prefix('inventario')
    ->name('inventario.')
    ->group(function () {

        // Consulta: todos los roles autenticados
        Route::get('/existencias', [ExistenciaController::class, 'index'])->name('existencias.index');
        Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');

        // Registro de movimientos: solo Gerente y Administrador
        Route::middleware(['role:GERENTE,ADMINISTRADOR'])->group(function () {
            Route::get('/movimientos/crear', [MovimientoController::class, 'create'])->name('movimientos.create');
            Route::post('/movimientos', [MovimientoController::class, 'store'])->name('movimientos.store');
        });
    });
