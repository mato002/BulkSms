<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sentry Data Source Name (DSN)
    |--------------------------------------------------------------------------
    |
    | Define the DSN that should be used to connect to Sentry. Leave this value
    | empty in non-production environments if you do not want to report events.
    |
    */
    'dsn' => env('SENTRY_LARAVEL_DSN'),

    /*
    |--------------------------------------------------------------------------
    | Sampling
    |--------------------------------------------------------------------------
    |
    | Configure the sampling rates for traces and profiles. These are expressed
    | as floats between 0.0 and 1.0. Adjust them to balance insight with cost.
    |
    */
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.2),

    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 0.2),

    /*
    |--------------------------------------------------------------------------
    | Environment & Release Metadata
    |--------------------------------------------------------------------------
    |
    | Use these values to tag events with environment or release identifiers.
    | They default to the standard Laravel APP_ENV and APP_VERSION values.
    |
    */
    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV')),

    'release' => env('SENTRY_RELEASE', env('APP_VERSION')),

    /*
    |--------------------------------------------------------------------------
    | Personally Identifiable Information (PII)
    |--------------------------------------------------------------------------
    |
    | If you would like to send users, IP addresses, and request headers to
    | Sentry, you may enable the flag below. Keep privacy implications in mind.
    |
    */
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    /*
    |--------------------------------------------------------------------------
    | Event Filtering
    |--------------------------------------------------------------------------
    |
    | Use "before_send" to drop events you do not want sent to Sentry. The
    | sample below suppresses noise from scheduled Artisan commands without
    | relying on the old "ignore_commands" option that was removed in SDK v4.
    |
    */
    'before_send' => function (\Sentry\Event $event, ?\Sentry\EventHint $hint = null) {
        if ($hint !== null && property_exists($hint, 'extra') && is_array($hint->extra ?? null)) {
            $command = $hint->extra['command'] ?? null;
            if ($command !== null && in_array($command, ['schedule:run', 'schedule:work'], true)) {
                return null; // swallow scheduler heartbeat events
            }
        }

        return $event;
    },

];


