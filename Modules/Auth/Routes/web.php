<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\LoginController;
use Modules\Auth\Controllers\UserController;


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
    Route::middleware(['auth', 'role:GERENTE,ADMINISTRADOR'])
        ->prefix('usuarios')
        ->name('usuarios.')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/crear', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{usuario}/editar', [UserController::class, 'edit'])->name('edit');
            Route::put('/{usuario}', [UserController::class, 'update'])->name('update');
            Route::patch('/{usuario}/estado', [UserController::class, 'toggleEstado'])->name('toggleEstado');
        });
});
