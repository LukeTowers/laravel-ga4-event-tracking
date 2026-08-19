<?php

namespace LukeTowers\GA4EventTracking\Tests\Unit;

use Illuminate\Support\Facades\Event;
use LukeTowers\GA4EventTracking\ShouldBroadcastToAnalytics;
use LukeTowers\GA4EventTracking\Tests\TestCase;

class RegistersListenerTest extends TestCase
{
    protected ?string $measurementId = 'G-123456789';

    protected ?string $apiSecret = '123456789';

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('ga4-event-tracking.measurement_id', $this->measurementId);
        $app['config']->set('ga4-event-tracking.api_secret', $this->apiSecret);
    }

    protected function bootWith(?string $measurementId, ?string $apiSecret): void
    {
        $this->measurementId = $measurementId;
        $this->apiSecret = $apiSecret;
        $this->refreshApplication();
    }

    protected function listenerIsRegistered(): bool
    {
        return Event::hasListeners(ShouldBroadcastToAnalytics::class);
    }

    public function test_listener_is_registered_when_measurement_id_and_api_secret_are_set()
    {
        $this->bootWith('G-123456789', '123456789');

        $this->assertTrue($this->listenerIsRegistered());
    }

    public function test_listener_is_not_registered_when_api_secret_is_missing()
    {
        $this->bootWith('G-123456789', null);

        $this->assertFalse($this->listenerIsRegistered());
    }

    public function test_listener_is_not_registered_when_measurement_id_is_missing()
    {
        $this->bootWith(null, '123456789');

        $this->assertFalse($this->listenerIsRegistered());
    }
}
