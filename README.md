# Laravel Google Analytics 4 Measurement Protocol Event Tracking

[![Version](https://img.shields.io/github/v/release/devproca/laravel-ga4-event-tracking?sort=semver&style=flat-square)](https://github.com/devproca/laravel-ga4-event-tracking/releases)
[![Tests](https://img.shields.io/github/actions/workflow/status/devproca/laravel-ga4-event-tracking/tests.yaml?&label=tests&style=flat-square)](https://github.com/devproca/laravel-ga4-event-tracking/actions)
[![License](https://img.shields.io/github/license/devproca/laravel-ga4-event-tracking?label=open%20source&style=flat-square)](https://packagist.org/packages/devpro/laravel-ga4-event-tracking)


Simplifies using the [Measurement Protocol for Google Analytics 4](https://developers.google.com/analytics/devguides/collection/protocol/ga4) to track events in Laravel applications.

## Installation

1) Install package via Composer

``` bash
composer require devpro/laravel-ga4-event-tracking
```

2) Set `MEASUREMENT_ID`  and `MEASUREMENT_PROTOCOL_API_SECRET` in your .env file.

> Copy from `Google Analytics > Admin > Data Streams > [Select Site] > Measurement ID` & `Google Analytics > Admin > Data Streams > [Select Site] > Measurement Protocol API secrets` respectively.

3) Optional: Publish the config / view files by running this command in your terminal:

``` bash
php artisan vendor:publish --tag=ga4-event-tracking
```

4) Include the `sendGA4ClientID` directive in your layout file after the Google Analytics Code tracking code.

```blade
<!-- Google Analytics Code -->
@sendClientID
<!-- </head> -->
```

The [`client_id`](https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference?client_type=gtag#payload_post_body) is required to send an event to Google Analytics. This package provides a Blade directive which you can put in your layout file after the Google Analytics Code tracking code. It grabs the current user's GA `client_id` from either the `ga()` or `gtag()` helper functions injected by Google Analytics and makes a POST request to your application to store the `client_id` in the session, which is later used by the `DispatchAnalyticsJob` when sending events to GA4.

If you do not use this blade directive, you will have to handle retrieving, storing, and sending the `client_id` yourself. You can use `GA$::setClientId($clientId)` to set the `client_id` manually.

## Usage

This package provides two ways to send events to Google Analytics 4:

### Directly via the `GA4` facade:

Sending event directly is as simple as calling the `sendEvent($eventData)` method on the `GA4` facade from anywhere in your backend to post event to Google Analytics 4. `$eventData` contains the name and params of the event as per this [reference page](https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference/events#login). For example:

```php
GA4::sendEvent([
    'name' => 'login',
    'params' => [
        'method' => 'Google',
    ],
]);
```

The `sendEvent()` method will return an array with the status of the request.


### Broadcast events to GA4 via the Laravel Event System

Just add the `ShouldBroadcastToAnalytics` interface to your event, and you're ready! You don't have to manually bind any listeners.

```php
<?php

namespace App\Events;

use App\Order;
use DevPro\GA4EventTracking\ShouldBroadcastToAnalytics;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderWasCreated implements ShouldBroadcastToAnalytics
{
    use Dispatchable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
```

There are two additional methods that lets you customize the call sent to GA4.

With the `broadcastGA4EventAs` method you can customize the name of the [Event Action](https://developers.google.com/analytics/devguides/collection/analyticsjs/field-reference#eventAction). By default, we use the class name with the class's namespace removed. This method gives you access to the underlying `GA4` class instance as well.

With the `withGA4Parameters` method you can set the parameters of the event being sent.

```php
<?php

namespace App\Events;

use App\Order;
use DevPro\GA4EventTracking\ShouldBroadcastToAnalytics;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderWasCreated implements ShouldBroadcastToAnalytics
{
    use Dispatchable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function withGA4Parameters(GA4 $ga4): array
    {
        $eventData = [
            'transaction_id' => $order->id,
            'value' => $order->amount_total,
            'currency' => 'USD',
            'tax' => $order->amount_tax,
            'shipping' => $order->amount_shipping,
            'items' => [],
            'event_category' => config('app.name'),
            'event_label' => 'Order Created',
        ];

        foreach ($order->items as $item) {
            $eventData['items'][] = [
                'id' => $item->sku ?: 'p-' . $item->product->id,
                'name' => $item->title,
                'brand' => $item->product->brand->name ?? '',
                'category' => $item->product->category->title ?? '',
                'quantity' => $item->quantity,
                'price' => $item->price,
                'variant' => $item->variant->title ?? '',
            ];
        }

        return $eventData;
    }

    public function broadcastGA4EventAs(GA4 $ga4): string
    {
        return 'purchase';
    }
}
```


### Handle framework and 3rd-party events

If you want to handle events where you can't add the `ShouldBroadcastToAnalytics` interface, you can manually register them in your `EventServiceProvider` using the `DispatchAnalyticsJob` listener.

```php
<?php

namespace App\Providers;

use DevPro\GA4EventTracking\Listeners\DispatchAnalyticsJob;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            DispatchAnalyticsJob::class,
        ],
    ];
}
```

### Debugging Mode

You can also enable [debugging mode](https://developers.google.com/analytics/devguides/collection/protocol/ga4/validating-events) by calling `enableDebugging()` method before calling the `sendEvent()` method. Like so - `GA4::enableDebugging()->sendEvent($eventData)`. The `sendEvent()` method will return the response (array) from Google Analytics request in that case.


## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please use the [Report a vulnerability](https://github.com/devproca/laravel-ga4-event-tracking/security/advisories/new) button instead of using the issue tracker.

## Credits:

This package is a fork of the following projects:

- [protonemedia/laravel-analytics-event-tracking](https://github.com/protonemedia/laravel-analytics-event-tracking): Original package, but only supports Universal Analytics.
- [daikazu/laravel-ga4-event-tracking](https://github.com/daikazu/laravel-ga4-event-tracking): Forked from the original package to support Google Analytics 4 but the package was not maintained and it was not compatible with Laravel 10.
- [accexs/laravel-ga4-event-tracking](https://github.com/accexs/laravel-ga4-event-tracking): Forked from `daikazu`'s package but was missing some features and was not cleanly forked.

## License

This package is open-sourced software licensed under the [MIT License](LICENSE).
