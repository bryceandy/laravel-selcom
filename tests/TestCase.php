<?php

namespace Bryceandy\Selcom\Tests;

use Bryceandy\Selcom\SelcomBaseServiceProvider;
use Illuminate\Foundation\Application;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set([
            'selcom.vendor' => '12345',
            'selcom.key' => 'abc',
            'selcom.secret' => 'abc',
        ]);
    }

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