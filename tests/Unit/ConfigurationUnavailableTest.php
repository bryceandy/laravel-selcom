<?php

namespace Bryceandy\Selcom\Tests\Unit;

use Bryceandy\Selcom\Exceptions\ConfigurationUnavailableException;
use Bryceandy\Selcom\Selcom;
use Orchestra\Testbench\TestCase;

class ConfigurationUnavailableTest extends TestCase
{
    /** @test */
    public function test_configuration_unavailability_should_throw_an_exception()
    {
        $this->expectException(ConfigurationUnavailableException::class);

        $selcomInstance = new Selcom;
    }
}
