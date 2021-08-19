<?php

namespace Bryceandy\Selcom\Tests;

use Bryceandy\Selcom\SelcomBaseServiceProvider;
use Illuminate\Foundation\Application;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Register service providers
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            SelcomBaseServiceProvider::class,
        ];
    }
}