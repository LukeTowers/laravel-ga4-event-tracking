<?php

return [

    /**
     * Enable sending events to GA.
     */
    'is_enabled' => true,

    /**
     * Your GA4 Measurement ID, looks like "G-XXXXXXXXXX", copy from:
     * Google Analytics > Admin > Data Streams > [Select Site] > Measurement ID
     * @see https://support.google.com/analytics/answer/9304153?hl=en for setup instructions.
     */
    'measurement_id' => env('GA4_MEASUREMENT_ID'),

    /**
     * Your GA4 Measurement Protocol API Secret, copy from:
     * Google Analytics > Admin > Data Streams > [Select Site] > Measurement Protocol API secrets
     * @see https://developers.google.com/analytics/devguides/collection/protocol/ga4/reference?client_type=gtag#payload_post_body
     */
    'api_secret' => env('GA4_MEASUREMENT_PROTOCOL_API_SECRET', null),

    /**
     * The session key to store the GA4 Client ID in.
     */
    'client_id_session_key' => 'ga4-event-tracking-client-id',

    /**
     * The session key to store the GA4 Session ID in.
     */
    'session_id_session_key' => 'ga4-event-tracking-session-id',

    /**
     * HTTP URI to post the Client ID to (from the Blade Directive).
     */
    'http_uri' => '/gaid',

    /*
     * This queue will be used to perform the API calls to GA.
     * Leave empty to use the default queue.
     */
    'queue_name' => '',

    /**
     * Send the ID of the authenticated user to GA.
     */
    'send_user_id' => false,
];
