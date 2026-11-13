    <?php

    use Illuminate\Support\Facades\Route;
    use Modules\Ventas\Controllers\VentaController;

    Route::middleware(['auth', 'role:GERENTE,ADMINISTRADOR,VENDEDOR'])
        ->prefix('ventas')
        ->name('ventas.')
        ->group(function () {

            // Estáticas PRIMERO (regla que ya nos mordió en Clientes)
            Route::get('/',      [VentaController::class, 'index'])->name('index');
            Route::get('/crear', [VentaController::class, 'create'])->name('create');
            Route::post('/',     [VentaController::class, 'store'])->name('store');

            // Dinámicas DESPUÉS, con whereNumber
            Route::get('/{venta}', [VentaController::class, 'show'])
                ->whereNumber('venta')->name('show');

            Route::get('/{venta}/comprobante', [VentaController::class, 'comprobante'])
                ->whereNumber('venta')->name('comprobante');

            Route::post('/{venta}/pagos', [VentaController::class, 'registrarPago'])
                ->whereNumber('venta')->name('pagos.store');

            // Cancelar: solo Gerente/Admin (permiso APROBAR en ventas)
            Route::post('/{venta}/cancelar', [VentaController::class, 'cancelar'])
                ->whereNumber('venta')
                ->middleware('role:GERENTE,ADMINISTRADOR')
                ->name('cancelar');
        });
