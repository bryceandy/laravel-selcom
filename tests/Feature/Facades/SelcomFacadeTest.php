<?php

namespace Bryceandy\Selcom\Tests\Feature\Facades;

use Bryceandy\Selcom\Exceptions\ConfigurationUnavailableException;
use Bryceandy\Selcom\Facades\Selcom;
use Bryceandy\Selcom\Tests\TestCase;
use Illuminate\Foundation\Application;

class SelcomFacadeTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('selcom.vendor', null);
    }

    /** @test */
    public function test_facade_requires_configuration_to_make_requests()
    {
        $this->expectException(ConfigurationUnavailableException::class);

        Selcom::makeRequest('', 'GET');
    }
}
