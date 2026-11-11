<?php

use Illuminate\Support\Facades\Route;
use Modules\Precios\Controllers\TipoCambioController;

Route::middleware(['auth'])
    ->prefix('precios/tipo-cambio')
    ->name('precios.tipo-cambio.')
    ->group(function () {
        Route::get('/', [TipoCambioController::class, 'index'])->name('index');

        Route::middleware(['role:GERENTE,ADMINISTRADOR'])->group(function () {
            Route::post('/refresh', [TipoCambioController::class, 'refresh'])->name('refresh');
            Route::post('/manual',  [TipoCambioController::class, 'storeManual'])->name('store-manual');
        });
    });
