<?php

use Illuminate\Support\Facades\Route;
use Modules\Cotizaciones\Controllers\CotizacionController;

Route::middleware(['auth', 'role:GERENTE,ADMINISTRADOR,VENDEDOR'])
    ->prefix('cotizaciones')
    ->name('cotizaciones.')
    ->group(function () {

        // 1) Estáticas PRIMERO
        Route::get('/',      [CotizacionController::class, 'index'])->name('index');
        Route::get('/crear', [CotizacionController::class, 'create'])->name('create');
        Route::post('/',     [CotizacionController::class, 'store'])->name('store');

        // 2) Dinámicas DESPUÉS, con whereNumber
        Route::get('/{cotizacion}', [CotizacionController::class, 'show'])
            ->whereNumber('cotizacion')->name('show');

        Route::middleware('role:GERENTE,ADMINISTRADOR')->group(function () {
            Route::get('/{cotizacion}/editar', [CotizacionController::class, 'edit'])
                ->whereNumber('cotizacion')->name('edit');
            Route::put('/{cotizacion}', [CotizacionController::class, 'update'])
                ->whereNumber('cotizacion')->name('update');
            Route::post('/{cotizacion}/anular', [CotizacionController::class, 'anular'])
                ->whereNumber('cotizacion')->name('anular');
        });
    });
