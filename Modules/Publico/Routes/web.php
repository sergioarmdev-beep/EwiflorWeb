<?php

use Illuminate\Support\Facades\Route;
use Modules\Publico\Controllers\PublicoController;

// Ruta raíz (catálogo público, sin autenticación). Se conserva el nombre
// 'catalogo.publico' para no romper enlaces existentes.
Route::get('/', [PublicoController::class, 'catalogoPublico'])
    ->name('catalogo.publico');

// Contacto con servicio técnico desde la web pública (cliente existente).
Route::post('/contactar-servicio-tecnico', [PublicoController::class, 'contactarServicioTecnico'])
    ->name('publico.contactar.servicio.tecnico');
