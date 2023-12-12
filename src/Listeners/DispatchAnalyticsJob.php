<?php

namespace DevPro\GA4EventTracking\Listeners;

use DevPro\GA4EventTracking\Http\ClientIdRepository;
use DevPro\GA4EventTracking\Jobs\SendEventToAnalytics;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class DispatchAnalyticsJob
{
    use InteractsWithQueue;

    public ClientIdRepository $clientIdRepository;

    public function __construct(ClientIdRepository $clientIdRepository)
    {
        $this->clientIdRepository = $clientIdRepository;
    }

    public function handle($event): void
    {
        $job = new SendEventToAnalytics($event, $this->clientIdRepository->get(), $this->userId());
        if ($queueName = config('ga4-event-tracking.tracking.queue_name')) {
            $job->onQueue($queueName);
        }

        dispatch($job);
    }

    private function userId(): ?string
    {
        if (!config('ga4-event-tracking.tracking.send_user_id', false)) {
            return null;
        }

        return Auth::id();
    }
}
