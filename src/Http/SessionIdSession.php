<?php

namespace LukeTowers\GA4EventTracking\Http;

use Illuminate\Session\Store;

class SessionIdSession implements SessionIdRepository
{
    private Store $session;

    private string $key;

    public function __construct(Store $session, string $key)
    {
        $this->session = $session;
        $this->key = $key;
    }

    /**
     * Stores the GA4 Session ID in the session.
     */
    public function update(string $sessionId): void
    {
        $this->session->put($this->key, $sessionId);
    }

    /**
     * Gets the GA4 Session ID from the session or generates one.
     */
    public function get(): ?string
    {
        return $this->session->get($this->key, fn () => $this->generateId());
    }

    /**
     * Generates a GA4 Session ID and stores it in the session.
     */
    private function generateId(): string
    {
        return tap(now()->timestamp, fn ($id) => $this->update($id));
    }
}
