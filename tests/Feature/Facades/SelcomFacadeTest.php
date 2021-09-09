<?php

namespace Bryceandy\Selcom\Tests\Feature\Facades;

use Bryceandy\Selcom\Exceptions\MissingDataException;
use Bryceandy\Selcom\Facades\Selcom;
use Bryceandy\Selcom\Tests\TestCase;

class SelcomFacadeTest extends TestCase
{
    /** @test */
    public function test_facade_requires_configuration_to_make_requests()
    {
        $this->app['config']->set('selcom.vendor', null);

        $this->expectException(MissingDataException::class);

        Selcom::makeRequest('', 'GET');
    }
}
