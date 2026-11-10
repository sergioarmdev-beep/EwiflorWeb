<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\LoginController;


Route::middleware('web')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
    });
});
