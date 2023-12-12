<?php

namespace DevPro\GA4EventTracking\Tests;

use DevPro\GA4EventTracking\ServiceProvider;

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
