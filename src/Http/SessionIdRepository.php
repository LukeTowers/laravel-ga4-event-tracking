<?php

namespace DevPro\GA4EventTracking\Http;

interface SessionIdRepository
{
    public function update(string $sessionId): void;

    public function get(): ?string;
}
