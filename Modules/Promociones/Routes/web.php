<?php

use Illuminate\Support\Facades\Route;
use Modules\Promociones\Controllers\ComboController;
use Modules\Promociones\Controllers\PromocionController;

// ==========================================
// RUTAS PRIVADAS (Solo Gerente y Administrador)
// ==========================================
Route::middleware(['auth', 'role:GERENTE,ADMINISTRADOR'])
    ->prefix('promociones')
    ->group(function () {

        // --- RUTAS ESTÁTICAS (antes de las dinámicas) ---

        // Promociones (descuentos por variante)
        Route::get('/', [PromocionController::class, 'index'])->name('promociones.index');
        Route::get('/crear', [PromocionController::class, 'create'])->name('promociones.create');
        Route::post('/', [PromocionController::class, 'store'])->name('promociones.store');

        // Combos (productos compuestos)
        Route::get('/combos', [ComboController::class, 'index'])->name('promociones.combos.index');
        Route::get('/combos/crear', [ComboController::class, 'create'])->name('promociones.combos.create');
        Route::post('/combos', [ComboController::class, 'store'])->name('promociones.combos.store');

        // --- RUTAS DINÁMICAS ---
        Route::get('/{promocion}/editar', [PromocionController::class, 'edit'])->name('promociones.edit');
        Route::put('/{promocion}', [PromocionController::class, 'update'])->name('promociones.update');
        Route::patch('/{promocion}/estado', [PromocionController::class, 'toggleEstado'])->name('promociones.toggle');
        Route::delete('/{promocion}', [PromocionController::class, 'destroy'])->name('promociones.destroy');

        Route::get('/combos/{combo}/editar', [ComboController::class, 'edit'])->name('promociones.combos.edit');
        Route::put('/combos/{combo}', [ComboController::class, 'update'])->name('promociones.combos.update');
        Route::patch('/combos/{combo}/estado', [ComboController::class, 'toggleEstado'])->name('promociones.combos.toggle');
    });
