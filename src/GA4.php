<?php

namespace LukeTowers\GA4EventTracking;

use LukeTowers\GA4EventTracking\Exceptions\MissingClientIdException;
use LukeTowers\GA4EventTracking\Exceptions\ReservedEventNameException;
use LukeTowers\GA4EventTracking\Exceptions\ReservedParameterNameException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GA4
{
    protected string $clientId = '';

    protected string $userId = '';

    protected string $timestampMicros = '';

    protected ?string $sessionId = null;

    protected array $userProperties = [];

    protected bool $debugging = false;

    protected string $eventAction = '';

    protected array $eventParams = [];

    /**
     * @see https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference?client_type=gtag#reserved_event_names
     */
    protected const RESERVED_EVENT_NAMES = [
        'ad_activeview',
        'ad_click',
        'ad_exposure',
        'ad_impression',
        'ad_query',
        'ad_reward',
        'adunit_exposure',
        'app_clear_data',
        'app_exception',
        'app_install',
        'app_remove',
        'app_store_refund',
        'app_update',
        'app_upgrade',
        'dynamic_link_app_open',
        'dynamic_link_app_update',
        'dynamic_link_first_open',
        'error',
        'firebase_campaign',
        'firebase_in_app_message_action',
        'firebase_in_app_message_dismiss',
        'firebase_in_app_message_impression',
        'first_open',
        'first_visit',
        'in_app_purchase',
        'notification_dismiss',
        'notification_foreground',
        'notification_open',
        'notification_receive',
        'notification_send',
        'os_update',
        'screen_view',
        'session_start',
        'user_engagement',
    ];

    /**
     * @see https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference?client_type=gtag#reserved_parameter_names
     */
    protected const RESERVED_PARAM_PREFIXES = [
        '_',
        'firebase_',
        'ga_',
        'google_',
        'gtag.',
    ];

    /**
     * @see https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference?client_type=gtag#reserved_user_property_names
     */
    protected const RESERVED_USER_PROPERTY_NAMES = [
        'first_open_time',
        'first_visit_time',
        'last_deep_link_referrer',
        'user_id',
        'first_open_after_install',
    ];
    protected const RESERVED_USER_PROPERTY_PREFIXES = [
        '_',
        'firebase_',
        'ga_',
        'google_',
    ];

    public function isConfigured(): bool
    {
        return config('ga4-event-tracking.measurement_id') !== null
            && config('ga4-event-tracking.api_secret') !== null;
    }

    public function isEnabled(): bool
    {
        return $this->isConfigured() && config('ga4-event-tracking.is_enabled', true);
    }

    public function setClientId(string $clientId): static
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function setTimestampMicros(string $timestampMicros): static
    {
        // @TODO: Perform validation on this to ensure it's a valid timestamp
        $this->timestampMicros = $timestampMicros;
        return $this;
    }

    public function setSessionId(string $sessionId): static
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setUserProperties(array $userProperties): static
    {
        $this->userProperties = $userProperties;
        return $this;
    }

    public function getUserProperties(): array
    {
        return $this->userProperties;
    }

    public function setEventAction(string $eventAction): void
    {
        $this->eventAction = $eventAction;
    }

    public function setEventParams(array $eventParams): void
    {
        if (!isset($eventParams['session_id']) && !is_null($this->sessionId)) {
            // Required to have events show up in session based reporting
            // @see https://developers.google.com/analytics/devguides/collection/protocol/ga4/sending-events?client_type=gtag#format_the_request
            $eventParams['session_id'] = $this->sessionId;
        }

        $this->eventParams = $eventParams;
    }

    public function enableDebugging(bool $toggle = true): static
    {
        $this->debugging = $toggle;
        return $this;
    }

    /**
     * @throws MissingClientIdException
     * @throws ReservedEventNameException
     * @throws ReservedParamNameException
     */
    public function sendEvent(array $eventData): array
    {
        return $this->sendEvents([$eventData]);
    }

    /**
     * @throws MissingClientIdException
     * @throws ReservedEventNameException
     * @throws ReservedParamNameException
     */
    public function sendEvents(array $events): array
    {
        if (!$this->isEnabled()) {
            return [
                'status' => false,
                'message' => 'GA4 Event Tracking is not configured.',
            ];
        }

        if (
            !$this->clientId
            && !$this->clientId = session(config('ga4-event-tracking.client_id_session_key'))
        ) {
            throw new MissingClientIdException;
        }

        $this->validateEvents($events);

        if (!empty($this->userProperties)) {
            $this->validateUserProperties($this->userProperties);
        }

        $requestData = [
            'client_id' => $this->clientId,
            'events' => $events,
        ];

        if (!empty($this->userId)) {
            $requestData['user_id'] = $this->userId;
        }

        if (!empty($this->timestampMicros)) {
            // @TODO: Perform validation on this to ensure that it's within the past 72 hours
            // @see https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference?client_type=gtag#payload_post_body
            $requestData['timestamp_micros'] = $this->timestampMicros;
        }

        if (!empty($this->userProperties)) {
            $requestData['user_properties'] = $this->userProperties;
        }

        $response = Http::withOptions([
            'query' => [
                'measurement_id' => config('ga4-event-tracking.measurement_id'),
                'api_secret' => config('ga4-event-tracking.api_secret'),
            ],
        ])->post($this->getRequestUrl(), $requestData);

        if ($this->debugging) {
            return $response->json();
        }

        return [
            'status' => $response->successful(),
        ];
    }

    protected function getRequestUrl(): string
    {
        $url = 'https://www.google-analytics.com';
        $url .= $this->debugging ? '/debug' : '';

        return $url . '/mp/collect';
    }

    public function sendAsSystemEvent(): void
    {
        $this->sendEvent([
            'name' => $this->eventAction,
            'params' => $this->eventParams,
        ]);
    }

    /**
     * @throws ReservedEventNameException if a reserved event name is used
     * @throws ReservedParameterNameException if a reserved parameter name is used
     * @throws Exception if more than 25 events are sent at once
     */
    public function validateEvents(array $events): array
    {
        if (count($events) > 25) {
            throw new \Exception('You can only send 25 events at a time to GA4.');
        }

        foreach ($events as $event) {
            $this->validateEvent($event);
        }

        return $events;
    }

    /**
     * @throws ReservedEventNameException if a reserved event name is used
     * @throws ReservedParameterNameException if a reserved parameter name is used
     */
    public function validateEvent(array $event): void
    {
        if (!isset($event['name'])) {
            throw new \Exception('Event name is required.');
        }

        if (in_array($event['name'], static::RESERVED_EVENT_NAMES)) {
            throw new ReservedEventNameException("The event name {$event['name']} is reserved for Google Analytics 4. Please use a different name.");
        }

        if (!empty($event['params'])) {
            $this->validateParams($event['params']);
        }
    }

    /**
     * @throws ReservedParameterNameException if a reserved parameter name is used
     */
    public function validateParams(array $params): void
    {
        foreach ($params as $key => $value) {
            if (Str::startsWith($key, static::RESERVED_PARAM_PREFIXES)) {
                throw new ReservedParameterNameException("The parameter name {$key} is reserved for Google Analytics 4. Please use a different name.");
            }
        }
    }

    /**
     * @throws Exception if a reserved user property name is used
     */
    public function validateUserProperties(array $params): void
    {
        foreach ($params as $key => $value) {
            if (in_array($key, static::RESERVED_USER_PROPERTY_NAMES)) {
                throw new \Exception("The user property name {$key} is reserved for Google Analytics 4. Please use a different name.");
            }

            if (Str::startsWith($key, static::RESERVED_USER_PROPERTY_PREFIXES)) {
                throw new \Exception("The user property name {$key} is reserved for Google Analytics 4. Please use a different name.");
            }
        }
    }
}
