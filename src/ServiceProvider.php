<?php

namespace DevPro\GA4EventTracking;

use DevPro\GA4EventTracking\Events\BroadcastEvent;
use DevPro\GA4EventTracking\Events\EventBroadcaster;
use DevPro\GA4EventTracking\Http\ClientIdRepository;
use DevPro\GA4EventTracking\Http\ClientIdSession;
use DevPro\GA4EventTracking\Http\StoreClientIdInSession;
use DevPro\GA4EventTracking\Listeners\DispatchAnalyticsJob;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ga4-event-tracking');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        // Only register the listener if the measurement_id and api_secret are set
        // to avoid unnecessary overhead.
        if (config('ga4-event-tracking.measurement_id') !== null
            && !config('ga4-event-tracking.api_secret') !== null
        ) {
            Event::listen(ShouldBroadcastToAnalytics::class, DispatchAnalyticsJob::class);
        }

        Blade::directive('sendGA4ClientID', function () {
            return "<?php echo view('ga4-event-tracking::sendClientID'); ?>";
        });
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'ga4-event-tracking');

        $this->app->singleton(EventBroadcaster::class, BroadcastEvent::class);
        $this->registerClientId();
        $this->registerAnalytics();
        $this->registerRoute();
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides()
    {
        return ['ga4-event-tracking'];
    }

    /**
     * Console-specific booting.
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('ga4-event-tracking.php'),
        ], 'ga4-event-tracking.config');

        // Publishing the views.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/ga4-event-tracking'),
        ], 'ga4-event-tracking.views');
    }

    protected function registerAnalytics()
    {
        $this->app->bind('ga4', function () {
            return new GA4();
        });
    }

    protected function registerClientId()
    {
        $this->app->singleton(ClientIdRepository::class, ClientIdSession::class);

        $this->app->bind('ga4-event-tracking.client-id', function () {
            return $this->app->make(ClientIdSession::class)->get();
        });

        $this->app->singleton(ClientIdSession::class, function () {
            return new ClientIdSession(
                $this->app->make('session.store'),
                config('ga4-event-tracking.client_id_session_key')
            );
        });
    }

    protected function registerRoute()
    {
        if ($httpUri = config('ga4-event-tracking.http_uri')) {
            Route::post($httpUri, StoreClientIdInSession::class)->middleware('web');
        }
    }
}
