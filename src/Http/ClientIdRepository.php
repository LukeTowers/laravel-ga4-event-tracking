<?php

namespace LukeTowers\GA4EventTracking\Http;

interface ClientIdRepository
{
    public function update(string $clientId): void;

    public function get(): ?string;
}
