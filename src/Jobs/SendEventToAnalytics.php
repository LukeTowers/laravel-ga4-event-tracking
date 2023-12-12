<?php

namespace DevPro\GA4EventTracking\Jobs;

use DevPro\GA4EventTracking\Events\EventBroadcaster;
use DevPro\GA4EventTracking\GA4;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEventToAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $event;

    public ?string $clientId;

    public ?string $userId;

    public function __construct($event, string $clientId = null, string $userId = null)
    {
        $this->event = $event;
        $this->clientId = $clientId;
        $this->userId = $userId;
    }

    public function handle(EventBroadcaster $broadcaster)
    {
        if ($this->clientId) {
            $broadcaster->withParameters(fn (GA4 $GA4) => $GA4->setClientId($this->clientId));
        }

        if ($this->userId) {
            $broadcaster->withParameters(fn (GA4 $GA4) => $GA4->setUserId($this->userId));
        }

        $broadcaster->handle($this->event);
    }
}
