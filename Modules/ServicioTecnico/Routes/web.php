<?php

use Illuminate\Support\Facades\Route;
use Modules\ServicioTecnico\Controllers\PublicoController;
use Modules\ServicioTecnico\Controllers\SolicitudController;

// ==========================================
// WEB PÚBLICA — sin login, sesión propia de cliente
// ==========================================
Route::middleware('web')->prefix('servicio-tecnico')->name('serviciotecnico.publico.')->group(function () {
    Route::get('/', [PublicoController::class, 'identificar'])->name('identificar');
    Route::post('/identificar', [PublicoController::class, 'validar'])
        ->middleware('throttle:10,1')
        ->name('validar');

    Route::middleware('sesion.cliente.publica')->group(function () {
        Route::get('/panel', [PublicoController::class, 'panel'])->name('panel');
        Route::get('/solicitar', [PublicoController::class, 'solicitar'])->name('solicitar');
        Route::post('/solicitar', [PublicoController::class, 'guardar'])
            ->middleware('throttle:10,1')
            ->name('guardar');
        Route::get('/mis-solicitudes/{solicitud}', [PublicoController::class, 'detalle'])->name('detalle');
        Route::post('/salir', [PublicoController::class, 'salir'])->name('salir');
    });
});

// ==========================================
// INTERNA — requiere autenticación
// ==========================================
Route::middleware(['auth'])->prefix('servicio-tecnico')->name('serviciotecnico.')->group(function () {

    // Técnico: bandeja de encargos y acciones sobre su solicitud
    Route::middleware('role:SERVICIO_TECNICO')->group(function () {
        Route::get('/bandeja', [SolicitudController::class, 'bandeja'])->name('bandeja');
        Route::post('/{solicitud}/aceptar', [SolicitudController::class, 'aceptar'])->name('aceptar');
        Route::post('/{solicitud}/cerrar', [SolicitudController::class, 'cerrar'])->name('cerrar');
    });

    // Vendedor: crear solicitud en nombre de un cliente
    Route::middleware('role:VENDEDOR,GERENTE,ADMINISTRADOR')->group(function () {
        Route::get('/nueva', [SolicitudController::class, 'nueva'])->name('nueva');
        Route::post('/nueva', [SolicitudController::class, 'guardarInterna'])->name('nueva.store');
    });

    // Gerente/Administrador: gestión global de movimientos del módulo
    Route::middleware('role:GERENTE,ADMINISTRADOR')->group(function () {
        Route::get('/gestion', [SolicitudController::class, 'gestion'])->name('gestion');
    });

    // Consulta de detalle para roles autorizados
    Route::get('/{solicitud}', [SolicitudController::class, 'show'])->name('show')
        ->middleware('role:VENDEDOR,SERVICIO_TECNICO,GERENTE,ADMINISTRADOR');
});
