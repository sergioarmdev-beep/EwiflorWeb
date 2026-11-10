<?php

namespace Modules\Catalogo\Providers;

use Illuminate\Support\ServiceProvider;

class CatalogoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Views', 'catalogo');
    }
}
