<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Karwan AI Emergency Switch
    |--------------------------------------------------------------------------
    |
    | Is switch ko false karne par chat, history aur clear endpoints safe
    | 503 response denge. Existing records delete ya modify nahi honge.
    |
    */

    'enabled' =>
        filter_var(
            env(
                'KARWAN_AI_ENABLED',
                true,
            ),
            FILTER_VALIDATE_BOOL,
        ),

    /*
    |--------------------------------------------------------------------------
    | Request and History Limits
    |--------------------------------------------------------------------------
    */

    'message_max_length' =>
        max(
            100,
            min(
                (int) env(
                    'KARWAN_AI_MESSAGE_MAX_LENGTH',
                    2000,
                ),
                5000,
            ),
        ),

    'history_limit' =>
        max(
            1,
            min(
                (int) env(
                    'KARWAN_AI_HISTORY_LIMIT',
                    30,
                ),
                100,
            ),
        ),

    /*
    |--------------------------------------------------------------------------
    | Public Route Rate Limits
    |--------------------------------------------------------------------------
    |
    | Laravel throttle format: maximum requests, minutes.
    |
    */

    'rate_limits' => [
        'chat' =>
            (string) env(
                'KARWAN_AI_CHAT_RATE_LIMIT',
                '20,1',
            ),

        'history' =>
            (string) env(
                'KARWAN_AI_HISTORY_RATE_LIMIT',
                '60,1',
            ),

        'clear' =>
            (string) env(
                'KARWAN_AI_CLEAR_RATE_LIMIT',
                '10,1',
            ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Ownership HMAC
    |--------------------------------------------------------------------------
    |
    | APP_KEY default signing key hai. Raw session ID database mein save nahi
    | hoti. Optional dedicated key environment se provide ki ja sakti hai.
    |
    */

    'session_hmac_key' =>
        (string) env(
            'KARWAN_AI_SESSION_HMAC_KEY',
            env(
                'APP_KEY',
                '',
            ),
        ),
];
