<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demio API Key & Secret
    |--------------------------------------------------------------------------
    |
    | The Api-Key and Api-Secret headers used to authenticate every request.
    | Find both at: https://my.demio.com/integrations/api
    |
    */
    'api_key' => env('DEMIO_API_KEY'),
    'api_secret' => env('DEMIO_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('DEMIO_BASE_URL', 'https://my.demio.com/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds to wait for a response before giving up.
    |
    */
    'timeout' => (int) env('DEMIO_TIMEOUT', 8),
];
