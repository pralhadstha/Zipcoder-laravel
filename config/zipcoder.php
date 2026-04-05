<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    |
    | Configure the Guzzle HTTP client used by all providers. Guzzle 7+
    | ships with Laravel and implements both PSR-18 (ClientInterface)
    | and PSR-17 (RequestFactoryInterface) out of the box.
    |
    | You can override the client by binding ClientInterface in the container.
    |
    */

    'http' => [
        'timeout' => env('ZIPCODER_HTTP_TIMEOUT', 10),
        'connect_timeout' => env('ZIPCODER_HTTP_CONNECT_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Credentials
    |--------------------------------------------------------------------------
    |
    | API keys and usernames required by each provider. Providers with
    | null credentials are skipped when building the chain.
    |
    | You can also register custom providers here:
    |
    |   'my-provider' => [
    |       'class' => \App\Zipcoder\MyProvider::class,
    |       'enabled' => true,
    |       // Extra keys are passed as constructor parameters
    |   ],
    |
    | Custom provider classes must implement Pralhad\Zipcoder\Contract\Provider.
    | They receive $client and $requestFactory automatically via the container.
    |
    */

    'providers' => [

        'geonames' => [
            'username' => env('GEONAMES_USERNAME'),
        ],

        'zippopotamus' => [
            'enabled' => true,
        ],

        'zipcodestack' => [
            'api_key' => env('ZIPCODESTACK_API_KEY'),
        ],

        'zipcodebase' => [
            'api_key' => env('ZIPCODEBASE_API_KEY'),
        ],

        'jp-postal-code' => [
            'enabled' => true,
            'locale' => env('JP_POSTAL_CODE_LOCALE', 'en'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Chain Order
    |--------------------------------------------------------------------------
    |
    | The order in which providers are tried during lookup. The first
    | provider that returns a result wins. Remove or reorder entries
    | to match your application's needs.
    |
    | Providers missing credentials are automatically excluded.
    | Custom providers can be added here by their config key name.
    |
    */

    'chain' => [
        'jp-postal-code',
        'zippopotamus',
        'geonames',
        'zipcodebase',
        'zipcodestack',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Enable caching to avoid repeated API calls for the same postal code.
    | Uses Laravel's default cache store via PSR-16 bridge.
    |
    */

    'cache' => [
        'enabled' => env('ZIPCODER_CACHE_ENABLED', true),
        'ttl' => env('ZIPCODER_CACHE_TTL', 86400), // 24 hours
        'store' => env('ZIPCODER_CACHE_STORE'), // null = default store
    ],

];
