<?php

use Illuminate\Support\Facades\Route;
use Modules\Sistema\Controllers\AuditoriaController;
use Modules\Sistema\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:GERENTE')->group(function () {
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    });
});
