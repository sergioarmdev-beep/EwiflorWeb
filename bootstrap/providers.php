<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Catalogo\Providers\CatalogoServiceProvider::class,
    Modules\Inventario\Providers\InventarioServiceProvider::class,
    Modules\Precios\Providers\PreciosServiceProvider::class,
    Modules\Sistema\Providers\SistemaServiceProvider::class,
    Modules\Clientes\Providers\ClientesServiceProvider::class,
    Modules\Cotizaciones\Providers\CotizacionesServiceProvider::class,
];
