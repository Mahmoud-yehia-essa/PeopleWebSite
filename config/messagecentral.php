<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Message Central VerifyNow Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Message Central VerifyNow API integration.
    |
    */

    'base_url' => env('MESSAGE_CENTRAL_BASE_URL', env('MESSAGECENTRAL_BASE_URL', 'https://cpaas.messagecentral.com')),

    'customer_id' => env('MESSAGE_CENTRAL_CUSTOMER_ID', 'C-45259547DE864C7'),

    'key' => env('MESSAGE_CENTRAL_KEY', env('MESSAGECENTRAL_KEY', '')),

    'auth_token' => env('MESSAGE_CENTRAL_AUTH_TOKEN') ?: env('MESSAGECENTRAL_AUTH_TOKEN', ''),
];
