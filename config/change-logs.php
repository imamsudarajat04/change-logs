<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Change Logs
    |--------------------------------------------------------------------------
    |
    | Enable or disable change logs tracking globally.
    |
    */
    'enabled' => env('CHANGE_LOGS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model that will be used to track who made the changes.
    |
    */
    'user_model' => env('CHANGE_LOGS_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Track IP Address
    |--------------------------------------------------------------------------
    |
    | Whether to track the IP address of the user making changes.
    |
    */
    'track_ip' => env('CHANGE_LOGS_TRACK_IP', true),

    /*
    |--------------------------------------------------------------------------
    | Track User Agent
    |--------------------------------------------------------------------------
    |
    | Whether to track the user agent of the user making changes.
    |
    */
    'track_user_agent' => env('CHANGE_LOGS_TRACK_USER_AGENT', true),

    /*
    |--------------------------------------------------------------------------
    | Log Per Field
    |--------------------------------------------------------------------------
    |
    | Determines how to log updates:
    | - true: Create separate log entry for each changed field (granular)
    | - false: Create single log entry with all changes (default, efficient)
    |
    */
    'log_per_field' => env('CHANGE_LOGS_LOG_PER_FIELD', false),

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should not be logged (e.g., passwords, sensitive data).
    |
    */
    'hidden_fields' => [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
        'secret',
        'secret_key',
        'email_verified_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Timestamp Fields
    |--------------------------------------------------------------------------
    |
    | Timestamp fields that should be excluded from logging to reduce noise.
    |
    */
    'exclude_timestamps' => env('CHANGE_LOGS_EXCLUDE_TIMESTAMPS', true),

    /*
    |--------------------------------------------------------------------------
    | Actions to Track
    |--------------------------------------------------------------------------
    |
    | Define which actions should be tracked.
    |
    */
    'track_actions' => [
        'create' => env('CHANGE_LOGS_TRACK_CREATE', true),
        'update' => env('CHANGE_LOGS_TRACK_UPDATE', true),
        'delete' => env('CHANGE_LOGS_TRACK_DELETE', true),
        'restore' => env('CHANGE_LOGS_TRACK_RESTORE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Whether to queue change log entries for better performance.
    |
    */
    'queue' => [
        'enabled' => env('CHANGE_LOGS_QUEUE_ENABLED', false),
        'connection' => env('CHANGE_LOGS_QUEUE_CONNECTION', null),
        'queue' => env('CHANGE_LOGS_QUEUE_NAME', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Configuration
    |--------------------------------------------------------------------------
    |
    | Automatically cleanup old change logs.
    |
    */
    'cleanup' => [
        'enabled' => env('CHANGE_LOGS_CLEANUP_ENABLED', false),
        'days' => env('CHANGE_LOGS_CLEANUP_DAYS', 365), # Keep logs for 1 year
    ],

    /*
    |--------------------------------------------------------------------------
    | Limit Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration option sets the maximum number of change logs
    |
    */
    'limit' => 20,
];