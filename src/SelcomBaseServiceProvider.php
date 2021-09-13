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
                __DIR__ . '/../config/selcom.php' => config_path('selcom.php'),
            ], 'selcom-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/selcom'),
            ], 'selcom-views');
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../config/selcom.php', 'selcom'
        );

        $this->loadAssets();
    }

    public function register()
    {
        $this->registerFacades();

        $this->registerRoutes();
    }

    private function loadAssets()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'selcom');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    private function registerFacades()
    {
        $this->app->singleton('Selcom', fn($app) => new \Bryceandy\Selcom\Selcom);
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