<?php

use Illuminate\Support\Facades\Route;
use Modules\Sistema\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
