<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\LoginController;
use Modules\Auth\Controllers\PerfilController;
use Modules\Auth\Controllers\SolicitudCuentaController;
use Modules\Auth\Controllers\UserController;


Route::middleware('web')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);

        // «¿No tienes cuenta? Solicita una»: máx. 5 envíos por minuto por IP
        Route::post('/solicitar-cuenta', [SolicitudCuentaController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('solicitudes.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        // Mi perfil (cualquier usuario autenticado). El middleware ForzarCambioContrasena
        // deja pasar estas rutas aunque el cambio de contraseña esté pendiente.
        Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil.show');
        Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('perfil.password');
    });

    Route::middleware(['auth', 'role:GERENTE,ADMINISTRADOR'])
        ->prefix('usuarios')
        ->name('usuarios.')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/crear', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');

            // Solicitudes de cuenta enviadas desde el login
            Route::get('/solicitudes', [SolicitudCuentaController::class, 'index'])->name('solicitudes.index');
            Route::patch('/solicitudes/{solicitud}/rechazar', [SolicitudCuentaController::class, 'rechazar'])->name('solicitudes.rechazar');

            Route::get('/{usuario}/editar', [UserController::class, 'edit'])->name('edit');
            Route::put('/{usuario}', [UserController::class, 'update'])->name('update');
            Route::patch('/{usuario}/estado', [UserController::class, 'toggleEstado'])->name('toggleEstado');
            Route::patch('/{usuario}/restablecer-password', [UserController::class, 'resetPassword'])->name('resetPassword');
        });
});
