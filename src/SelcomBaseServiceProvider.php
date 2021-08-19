<?php

namespace Bryceandy\Selcom;

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
}