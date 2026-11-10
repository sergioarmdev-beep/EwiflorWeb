<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalogo\Controllers\ProductoController;

// RUTA PÚBLICA (Visitantes / Usuario web)
Route::get('/', [ProductoController::class, 'catalogoPublico'])->name('catalogo.publico');


// RUTAS PRIVADAS (Requieren login)
Route::middleware(['auth'])->prefix('catalogo')->group(function () {
    Route::get('/productos', [ProductoController::class, 'index'])->name('catalogo.productos.index');
    Route::get('/productos/crear', [ProductoController::class, 'create'])->name('catalogo.productos.create');
    Route::post('/productos', [ProductoController::class, 'store'])->name('catalogo.productos.store');
});
