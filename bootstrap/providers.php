<?php

use App\Providers\AppServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Catalogo\Providers\CatalogoServiceProvider;
use Modules\Clientes\Providers\ClientesServiceProvider;
use Modules\Comisiones\Providers\ComisionesServiceProvider;
use Modules\Cotizaciones\Providers\CotizacionesServiceProvider;
use Modules\Dashboard\Providers\DashboardServiceProvider;
use Modules\Inventario\Providers\InventarioServiceProvider;
use Modules\Precios\Providers\PreciosServiceProvider;
use Modules\Promociones\Providers\PromocionesServiceProvider;
use Modules\Publico\Providers\PublicoServiceProvider;
use Modules\ServicioTecnico\Providers\ServicioTecnicoServiceProvider;
use Modules\Sistema\Providers\SistemaServiceProvider;
use Modules\Sucursales\Providers\SucursalesServiceProvider;
use Modules\Ventas\Providers\VentasServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    CatalogoServiceProvider::class,
    InventarioServiceProvider::class,
    PreciosServiceProvider::class,
    PromocionesServiceProvider::class,
    DashboardServiceProvider::class,
    PublicoServiceProvider::class,
    SistemaServiceProvider::class,
    ServicioTecnicoServiceProvider::class,
    ClientesServiceProvider::class,
    CotizacionesServiceProvider::class,
    VentasServiceProvider::class,
    SucursalesServiceProvider::class,
    ComisionesServiceProvider::class,
];
