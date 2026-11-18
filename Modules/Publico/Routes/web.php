<?php

use Illuminate\Support\Facades\Route;
use Modules\Publico\Controllers\PublicoController;

// Ruta raíz (inicio público, sin autenticación). Se conserva el nombre
// 'catalogo.publico' para no romper enlaces existentes.
Route::get('/', [PublicoController::class, 'catalogoPublico'])
    ->name('catalogo.publico');

// Página completa del catálogo (búsqueda + filtro por categoría: ?categoria=panaderia).
Route::get('/catalogo-publico', [PublicoController::class, 'catalogo'])
    ->name('publico.catalogo');

// Página "Nosotros" (propuesta + información institucional).
Route::get('/nosotros', [PublicoController::class, 'nosotros'])
    ->name('publico.nosotros');

// Página independiente de servicio técnico.
Route::get('/soporte-tecnico', [PublicoController::class, 'servicioTecnico'])
    ->name('publico.servicio.tecnico');

// Contacto con servicio técnico desde la web pública (cliente existente).
Route::post('/contactar-servicio-tecnico', [PublicoController::class, 'contactarServicioTecnico'])
    ->name('publico.contactar.servicio.tecnico');
