<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\GoogleAuthController;
use Modules\Auth\Controllers\LoginController;
use Modules\Auth\Controllers\NotificacionController;
use Modules\Auth\Controllers\PerfilController;
use Modules\Auth\Controllers\RecuperacionContrasenaController;
use Modules\Auth\Controllers\SolicitudCuentaController;
use Modules\Auth\Controllers\SolicitudRestablecimientoController;
use Modules\Auth\Controllers\UserController;

Route::middleware('web')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        // 10 envíos por minuto por IP: frena fuerza bruta aunque el bloqueo
        // de cuenta (3 fallos / 10 min) todavía no haya actuado.
        Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');

        // OAuth de Google: solo login de cuentas existentes, nunca crea cuentas.
        Route::get('/login/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('login.google');
        Route::get('/login/google/callback', [GoogleAuthController::class, 'handleCallback'])
            ->name('login.google.callback');

        // «Olvidé mi contraseña»: correo + celular registrados. Máx. 5 por minuto.
        Route::get('/olvide-mi-contrasena', [RecuperacionContrasenaController::class, 'show'])
            ->name('password.olvide');
        Route::post('/olvide-mi-contrasena', [RecuperacionContrasenaController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('password.olvide.store');

        // «¿No tienes cuenta? Solicita una»: máx. 5 envíos por minuto por IP
        Route::post('/solicitar-cuenta', [SolicitudCuentaController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('solicitudes.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        // Mi perfil (cualquier usuario autenticado). El middleware ForzarCambioContrasena
        // deja pasar estas rutas aunque el cambio de contraseña esté pendiente.
        Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil.show');
        Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('perfil.password');

        // Centro de notificaciones in-app
        Route::get('/notificaciones', [NotificacionController::class, 'index'])
            ->name('notificaciones.index');
        // Las rutas estáticas van antes de /{notificacion}/leer.
        Route::patch('/notificaciones/leer-todas', [NotificacionController::class, 'leerTodas'])
            ->name('notificaciones.leer-todas');
        Route::patch('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'leer'])
            ->name('notificaciones.leer');
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

            // «Olvidé mi contraseña»: el GERENTE ve todas; el ADMINISTRADOR solo
            // las de VENDEDOR/SERVICIO_TECNICO de su sucursal (en el controller).
            Route::get('/solicitudes-contrasena', [SolicitudRestablecimientoController::class, 'index'])
                ->name('solicitudes-contrasena.index');
            Route::patch('/solicitudes-contrasena/{solicitud}/atender', [SolicitudRestablecimientoController::class, 'atender'])
                ->name('solicitudes-contrasena.atender');
            Route::patch('/solicitudes-contrasena/{solicitud}/descartar', [SolicitudRestablecimientoController::class, 'descartar'])
                ->name('solicitudes-contrasena.descartar');

            // Rutas estáticas antes de /{usuario}
            Route::patch('/{usuario}/desbloquear', [UserController::class, 'desbloquear'])->name('desbloquear');
            Route::get('/{usuario}/editar', [UserController::class, 'edit'])->name('edit');
            Route::put('/{usuario}', [UserController::class, 'update'])->name('update');
            Route::patch('/{usuario}/estado', [UserController::class, 'toggleEstado'])->name('toggleEstado');
            Route::patch('/{usuario}/restablecer-password', [UserController::class, 'resetPassword'])->name('resetPassword');
        });
});
