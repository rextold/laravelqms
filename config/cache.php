<?php

use Illuminate\Support\Str;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This controls the default cache store used by the framework.
    |
    */
    'default' => env('CACHE_STORE', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    */
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => env('CACHE_FILE_PATH', storage_path('framework/cache/data')),
            'lock_path' => env('CACHE_LOCK_PATH', storage_path('framework/cache/data')),
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => env('CACHE_DB_CONNECTION', env('DB_CONNECTION')),
            'lock_connection' => env('CACHE_DB_LOCK_CONNECTION', env('DB_CONNECTION')),
        ],

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    */
    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),
];
