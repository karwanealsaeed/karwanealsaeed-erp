<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Karwan AI Emergency Switch
    |--------------------------------------------------------------------------
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
    | Provider Foundation
    |--------------------------------------------------------------------------
    |
    | Phase 8A3A ships only the local provider. External providers remain
    | disabled and no external API request is made.
    |
    */

    'provider' =>
        (string) env(
            'KARWAN_AI_PROVIDER',
            'local',
        ),

    'model' =>
        env(
            'KARWAN_AI_MODEL',
        ),

    'external_enabled' =>
        filter_var(
            env(
                'KARWAN_AI_EXTERNAL_ENABLED',
                false,
            ),
            FILTER_VALIDATE_BOOL,
        ),

    'fallback_enabled' =>
        filter_var(
            env(
                'KARWAN_AI_FALLBACK_ENABLED',
                true,
            ),
            FILTER_VALIDATE_BOOL,
        ),

    'timeout_seconds' =>
        max(
            1,
            min(
                (int) env(
                    'KARWAN_AI_TIMEOUT_SECONDS',
                    10,
                ),
                60,
            ),
        ),

    'max_output_characters' =>
        max(
            100,
            min(
                (int) env(
                    'KARWAN_AI_MAX_OUTPUT_CHARACTERS',
                    4000,
                ),
                12000,
            ),
        ),

    'allowed_providers' => [
        'local',
        'openai',
        'gemini',
    ],

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
