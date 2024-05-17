<?php

namespace DevPro\GA4EventTracking\Listeners;

use DevPro\GA4EventTracking\Http\ClientIdRepository;
use DevPro\GA4EventTracking\Http\SessionIdRepository;
use DevPro\GA4EventTracking\Jobs\SendEventToAnalytics;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class DispatchAnalyticsJob
{
    use InteractsWithQueue;

    public ClientIdRepository $clientIdRepository;
    public SessionIdRepository $sessionIdRepository;

    public function __construct(ClientIdRepository $clientIdRepository, SessionIdRepository $sessionIdRepository)
    {
        $this->clientIdRepository = $clientIdRepository;
        $this->sessionIdRepository = $sessionIdRepository;
    }

    public function handle($event): void
    {
        $job = new SendEventToAnalytics($event, $this->clientIdRepository->get(), $this->userId(), $this->sessionIdRepository->get());
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
