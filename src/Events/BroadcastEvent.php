<?php

namespace LukeTowers\GA4EventTracking\Events;

use Carbon\Carbon;
use LukeTowers\GA4EventTracking\GA4;

class BroadcastEvent implements EventBroadcaster
{
    private GA4 $GA4;

    public function __construct(GA4 $GA4)
    {
        $this->GA4 = $GA4;
    }

    public function withParameters(callable $callback): self
    {
        $callback($this->GA4);

        return $this;
    }

    public function handle($event): void
    {
        $eventAction = method_exists($event, 'broadcastGA4EventAs')
            ? $event->broadcastGA4EventAs($this->GA4)
            : str(class_basename($event))->snake()->toString();

        $this->GA4->setEventAction($eventAction);

        if (method_exists($event, 'withGA4Parameters')) {
            $this->GA4->setEventParams($event->withGA4Parameters($this->GA4));
        }

        $occurredAt = Carbon::now();
        if (method_exists($event, 'eventOccurredAt')) {
            $occurredAt = $event->eventOccurredAt();
        }
        if ($occurredAt instanceof Carbon) {
            $this->GA4->setTimestampMicros($occurredAt->timestamp . $occurredAt->micro);
        }

        $this->GA4->sendAsSystemEvent();
    }
}
