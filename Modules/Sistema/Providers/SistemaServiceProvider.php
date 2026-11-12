<?php

namespace Modules\Sistema\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SistemaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Views', 'sistema');
    }
}
