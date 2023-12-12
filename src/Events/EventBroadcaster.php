<?php

namespace DevPro\GA4EventTracking\Events;

interface EventBroadcaster
{
    public function handle($event);

    public function withParameters(callable $callback): self;
}
