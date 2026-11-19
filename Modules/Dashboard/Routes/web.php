<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/estadisticas/ventas-lineas', [DashboardController::class, 'lineasVentas'])->name('dashboard.ventas-lineas');
});
