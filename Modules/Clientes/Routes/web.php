<?php

use Illuminate\Support\Facades\Route;
use Modules\Clientes\Controllers\ClienteController;

Route::middleware(['auth'])->prefix('clientes')->name('clientes.')->group(function () {

    Route::get('/', [ClienteController::class, 'index'])->name('index');

    Route::middleware('role:GERENTE,ADMINISTRADOR,VENDEDOR')->group(function () {
        Route::get('/crear', [ClienteController::class, 'create'])->name('create');
        Route::post('/', [ClienteController::class, 'store'])->name('store');
    });

    Route::get('/{cliente}', [ClienteController::class, 'show'])
        ->whereNumber('cliente')
        ->name('show');

    Route::middleware('role:GERENTE,ADMINISTRADOR,VENDEDOR')->group(function () {
        Route::get('/{cliente}/editar', [ClienteController::class, 'edit'])
            ->whereNumber('cliente')
            ->name('edit');
        Route::put('/{cliente}', [ClienteController::class, 'update'])
            ->whereNumber('cliente')
            ->name('update');
    });

    Route::patch('/{cliente}/estado', [ClienteController::class, 'toggleEstado'])
        ->whereNumber('cliente')
        ->middleware('role:GERENTE,ADMINISTRADOR')
        ->name('toggleEstado');
});
