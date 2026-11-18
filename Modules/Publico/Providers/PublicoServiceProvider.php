<?php

namespace Modules\Publico\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PublicoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../Views',
            'publico'
        );

        Route::middleware('web')->group(
            __DIR__ . '/../Routes/web.php'
        );
    }
}
