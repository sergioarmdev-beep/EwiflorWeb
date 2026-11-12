<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalogo\Controllers\ProductoController;
use Modules\Catalogo\Controllers\MarcaController;
use Modules\Catalogo\Controllers\StockController;

// ==========================================
// RUTA PÚBLICA (Visitantes / Usuario web)
// ==========================================
Route::get('/catalogo-publico', [ProductoController::class, 'catalogoPublico'])->name('catalogo.publico');

// ==========================================
// RUTAS PRIVADAS (Requieren Login)
// ==========================================
Route::middleware(['auth'])->prefix('catalogo')->group(function () {

    // Todos los logueados pueden ver el listado de productos
    Route::get('/productos', [ProductoController::class, 'index'])->name('catalogo.productos.index');

    // Consulta de stock por producto (todos los logueados)
    Route::get('/productos/{producto}/stock', [StockController::class, 'show'])->name('catalogo.stock.show');

    // Rutas exclusivas para Gerente y Administrador
    Route::middleware(['role:GERENTE,ADMINISTRADOR'])->group(function () {

        // --- RUTAS DE PRODUCTOS ---
        Route::get('/productos/crear', [ProductoController::class, 'create'])->name('catalogo.productos.create');
        Route::post('/productos', [ProductoController::class, 'store'])->name('catalogo.productos.store');
        Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('catalogo.productos.edit');
        Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('catalogo.productos.update');
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('catalogo.productos.destroy'); // <-- Faltaba
        Route::patch('/productos/{producto}/estado', [ProductoController::class, 'toggleEstado'])->name('catalogo.productos.toggle'); // <-- Faltaba

        // --- STOCK ---
        Route::post('/productos/{producto}/stock/entrada', [StockController::class, 'entrada'])->name('catalogo.stock.entrada');

        // --- RUTAS DE MARCAS ---
        Route::get('/marcas', [MarcaController::class, 'index'])->name('catalogo.marcas.index');
        Route::post('/marcas', [MarcaController::class, 'store'])->name('catalogo.marcas.store');
        Route::put('/marcas/{marca}', [MarcaController::class, 'update'])->name('catalogo.marcas.update');
        Route::delete('/marcas/{marca}', [MarcaController::class, 'destroy'])->name('catalogo.marcas.destroy');
    });
});
