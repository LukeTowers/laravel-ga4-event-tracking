<?php

namespace LukeTowers\GA4EventTracking\Tests\Unit;

use LukeTowers\GA4EventTracking\Exceptions\ReservedEventNameException;
use LukeTowers\GA4EventTracking\Facades\GA4;
use LukeTowers\GA4EventTracking\Tests\TestCase;

class SendGA4EventTest extends TestCase
{
    public function test_it_can_send_a_ga4_event()
    {
        $test = GA4::setClientId('123456789')
            ->sendEvent([
                'name' => 'test_event',
                'params' => [
                    'method' => 'none',
                    'some_id' => '123',
                    'some_other_id' => '456',

                ],
            ]);

        $this->assertTrue($test['status']);
    }

    public function test_if_event_name_is_reserved()
    {
        $this->expectException(ReservedEventNameException::class);

        GA4::setClientId('123456789')
            ->sendEvent([
                'name' => 'ad_click',
            ]);
    }
}
