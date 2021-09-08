<?php

namespace Bryceandy\Selcom;

use Bryceandy\Selcom\Facades\Selcom;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SelcomBaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/selcom.php' => config_path('selcom.php')
            ], 'selcom-config');
        }
    }

    public function register()
    {
        $this->registerFacades();

        $this->registerRoutes();
    }

    private function registerFacades()
    {
        $this->app->singleton('Selcom', fn($app) => new Selcom);
    }

    private function registerRoutes()
    {
        $prefix = Selcom::prefix();

        Route::group(
            compact('prefix'),
            fn() => $this->loadRoutesFrom(__DIR__ . '/../routes/web.php')
        );
    }
}