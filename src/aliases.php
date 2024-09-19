<?php

namespace LukeTowers\GA4EventTracking;

/**
 * Alias DevPro\GA4EventTracking
 */
$classes = [
    'Events\BroadcastEvent',
    'Events\EventBroadcaster',
    'Exceptions\MissingClientIdException',
    'Exceptions\ReservedEventNameException',
    'Exceptions\ReservedParameterNameException',
    'Facades\GA4',
    'Http\ClientIdRepository',
    'Http\ClientIdSession',
    'Http\SessionIdRepository',
    'Http\SessionIdSession',
    'Http\StoreClientIdInSession',
    'Jobs\SendEventToAnalytics',
    'Listeners\DispatchAnalyticsJob',
    'ServiceProvider',
    'ShouldBroadcastToAnalytics',
];

foreach ($classes as $class) {
    class_alias("LukeTowers\\GA4EventTracking\\$class", "DevPro\\GA4EventTracking\\$class");
}
