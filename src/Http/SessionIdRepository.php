<?php

namespace LukeTowers\GA4EventTracking\Http;

interface SessionIdRepository
{
    public function update(string $sessionId): void;

    public function get(): ?string;
}
