<?php

use Illuminate\Support\Facades\Route;
use Modules\Sistema\Controllers\AuditoriaController;

Route::middleware(['auth'])->group(function () {
    Route::middleware('role:GERENTE')->group(function () {
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    });
});
