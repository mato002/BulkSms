<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | By default, Laravel will throttle requests. You may change these
    | settings based on the needs of your application.
    |
    */

    'for' => [
        'api' => [
            // Use a safe identifier that works in both HTTP and CLI contexts
            Limit::perMinute(60)->by(php_sapi_name() === 'cli' ? 'cli' : ($_SERVER['REMOTE_ADDR'] ?? 'unknown')),
        ],
    ],

];




