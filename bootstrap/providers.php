<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Catalogo\Providers\CatalogoServiceProvider::class,
    Modules\Inventario\Providers\InventarioServiceProvider::class,
];
