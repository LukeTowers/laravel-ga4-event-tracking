<?php

namespace LukeTowers\GA4EventTracking\Facades;

use Illuminate\Support\Facades\Facade;

class GA4 extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ga4';
    }
}
