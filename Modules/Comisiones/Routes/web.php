    <?php

    use Illuminate\Support\Facades\Route;
    use Modules\Comisiones\Controllers\ComisionController;

    Route::middleware(['auth', 'role:GERENTE,ADMINISTRADOR'])
        ->prefix('comisiones')
        ->name('comisiones.')
        ->group(function () {

            Route::get('/', [ComisionController::class, 'index'])->name('index');

            // Generar comisiones de un año/mes para los vendedores del alcance.
            Route::post('/generar', [ComisionController::class, 'generar'])->name('generar');

            // Marcar una comisión como pagada.
            Route::post('/{comision}/pagar', [ComisionController::class, 'pagar'])
                ->whereNumber('comision')
                ->name('pagar');
        });
