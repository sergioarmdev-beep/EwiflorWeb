<?php

use Illuminate\Support\Facades\Route;
use Modules\Publico\Controllers\PublicoController;

// Ruta raíz (inicio público, sin autenticación). Se conserva el nombre
// 'catalogo.publico' para no romper enlaces existentes.
Route::get('/', [PublicoController::class, 'inicio'])
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
    ->middleware('throttle:5,1')
    ->name('publico.contactar.servicio.tecnico');

// Página de detalle de producto público (SEO y compartición).
Route::get('/producto/{id}', [PublicoController::class, 'productoDetalle'])
    ->name('publico.producto.detalle');
