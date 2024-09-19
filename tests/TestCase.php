<?php

namespace LukeTowers\GA4EventTracking\Tests;

use LukeTowers\GA4EventTracking\ServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }
}
