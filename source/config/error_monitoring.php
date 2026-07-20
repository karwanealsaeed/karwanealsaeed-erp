<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

return [
    'enabled' =>
        filter_var(
            env(
                'SYSTEM_ERROR_MONITORING_ENABLED',
                true,
            ),
            FILTER_VALIDATE_BOOLEAN,
        ),

    'capture_testing' =>
        filter_var(
            env(
                'SYSTEM_ERROR_CAPTURE_TESTING',
                false,
            ),
            FILTER_VALIDATE_BOOLEAN,
        ),

    'alert_cooldown_minutes' =>
        max(
            5,
            (int) env(
                'SYSTEM_ERROR_ALERT_COOLDOWN_MINUTES',
                60,
            ),
        ),

    'resolved_retention_days' =>
        max(
            1,
            (int) env(
                'SYSTEM_ERROR_RESOLVED_RETENTION_DAYS',
                30,
            ),
        ),

    'log_retention_days' =>
        max(
            1,
            (int) env(
                'SYSTEM_LOG_RETENTION_DAYS',
                14,
            ),
        ),

    'raw_log_tail_lines' =>
        max(
            50,
            (int) env(
                'SYSTEM_LOG_TAIL_LINES',
                300,
            ),
        ),

    'max_message_length' =>
        2000,

    'max_trace_length' =>
        12000,

    'alert_severities' => [
        'critical',
        'error',
    ],

    'ignored_exceptions' => [
        ValidationException::class,
        AuthenticationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
    ],
];