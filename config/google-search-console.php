<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Application Name
    |--------------------------------------------------------------------------
    |
    | This is the application name that will be used when making API requests
    | to Google Search Console. You can change this to identify your
    | application in the Google API console.
    |
    */
    'application_name' => env('GOOGLE_SEARCH_CONSOLE_APP_NAME', 'AI Brand Rank'),

    /*
    |--------------------------------------------------------------------------
    | Default OAuth Scopes
    |--------------------------------------------------------------------------
    |
    | These are the default OAuth scopes that will be requested when
    | authenticating with Google. The package uses the Webmasters scope
    | for accessing Search Console data.
    |
    */
    'scopes' => [
        \Google\Service\Webmasters::WEBMASTERS,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests. You can adjust this based
    | on your needs. A higher timeout may be needed for large data queries.
    |
    */
    'timeout' => env('GOOGLE_SEARCH_CONSOLE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Default Retry Attempts
    |--------------------------------------------------------------------------
    |
    | The number of times to retry failed API requests before giving up.
    | This can help with transient network issues or temporary API errors.
    |
    */
    'retry_attempts' => env('GOOGLE_SEARCH_CONSOLE_RETRY_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Default Search Analytics Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for search analytics queries. These can be overridden
    | when making individual queries.
    |
    */
    'search_analytics' => [
        'default_row_limit' => 1000,
        'default_dimensions' => [],
        'default_date_range' => [
            'start_date' => '-30 days',
            'end_date' => 'yesterday',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Sitemap Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for sitemap operations.
    |
    */
    'sitemaps' => [
        'default_type' => 'WEB',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default URL Inspection Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for URL inspection operations.
    |
    */
    'url_inspection' => [
        'default_language_code' => 'en-US',
    ],

    'cache' => [
        'enabled' => env('GOOGLE_SEARCH_CONSOLE_CACHE_ENABLED', false),
        'ttl' => env('GOOGLE_SEARCH_CONSOLE_CACHE_TTL', 3600), // 1 hour in seconds
        'prefix' => env('GOOGLE_SEARCH_CONSOLE_CACHE_PREFIX', 'gsc_'),
    ],

    'debug' => env('GOOGLE_SEARCH_CONSOLE_DEBUG', false),
];
