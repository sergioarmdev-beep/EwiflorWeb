
<?php

use Illuminate\Support\Facades\Route;

// Redirigir la raíz al catálogo público
Route::get('/', function () {
    return redirect()->route('catalogo.publico');
});
