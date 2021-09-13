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
            'selcom.vendor' => '12345ABC',
            'selcom.key' => 'ABCDE',
            'selcom.secret' => 'ABCDE',
            'selcom.colors.header' => '#FF0012',
            'selcom.colors.link' => '#FF0012',
            'selcom.colors.button' => '#FF0012',
            'selcom.expiry' => 60,
            'database.default' => 'testdb',
            'database.connections.testdb' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
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