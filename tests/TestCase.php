<?php

namespace Bryceandy\Selcom\Tests;

use Bryceandy\Selcom\SelcomBaseServiceProvider;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public PromiseInterface $createOrderResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createOrderResponse = Http::response(json_decode(
            file_get_contents(__DIR__ . '/stubs/create-order-response.json'),
            true
        ));

        // Fake all sms requests to the API
        Http::fake([
            'https://apigwtest.selcommobile.com/v1/checkout/create-order-minimal' => $this->createOrderResponse,
            'https://apigwtest.selcommobile.com/v1/checkout/create-order' => $this->createOrderResponse,
        ]);
    }

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