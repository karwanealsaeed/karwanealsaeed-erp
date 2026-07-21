<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AiSetting;
use App\Models\AiUsageLog;
use App\Services\KarwanAi\LocalKarwanAiProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class KarwanAiManager
{
    public function __construct(
        private readonly LocalKarwanAiProvider $localProvider,
    ) {
    }

    public function answer(
        string $message,
        ?string $sessionToken = null,
        ?int $customerId = null,
    ): array {
        $requestUuid =
            (string) Str::uuid();

        $startedAt =
            hrtime(
                true,
            );

        $setting =
            $this->setting();

        $requestedProvider =
            Str::lower(
                trim(
                    (string) (
                        $setting?->provider
                        ?: config(
                            'karwan_ai.provider',
                            'local',
                        )
                    ),
                ),
            );

        if ($requestedProvider === '') {
            $requestedProvider =
                'local';
        }

        $actualProvider =
            $this->localProvider->name();

        $model =
            $setting?->model
            ?: config(
                'karwan_ai.model',
            );

        $fallbackUsed =
            false;

        $status =
            'success';

        $errorCode =
            null;

        $errorMessage =
            null;

        if ($requestedProvider !== 'local') {
            $fallbackUsed =
                true;

            $status =
                'fallback';

            $externalEnabled =
                (bool) config(
                    'karwan_ai.external_enabled',
                    false,
                )
                && (bool) (
                    $setting?->external_enabled
                    ?? false
                );

            $errorCode =
                $externalEnabled
                    ? 'PROVIDER_NOT_IMPLEMENTED'
                    : 'EXTERNAL_PROVIDER_DISABLED';

            $errorMessage =
                $externalEnabled
                    ? 'Requested external provider is not implemented.'
                    : 'External AI providers are disabled.';
        }

        try {
            $answer =
                $this->localProvider->answer(
                    $message,
                );

            if (
                (bool) (
                    $answer['fallback_used']
                    ?? false
                )
            ) {
                $fallbackUsed =
                    true;

                if ($status === 'success') {
                    $status =
                        'fallback';
                }
            }

            if (
                isset(
                    $answer['error_code'],
                )
                && $answer['error_code']
            ) {
                $errorCode =
                    (string) $answer[
                        'error_code'
                    ];

                $errorMessage =
                    (string) (
                        $answer['error_message']
                        ?? 'Local provider fallback was used.'
                    );
            }
        } catch (Throwable $exception) {
            report(
                $exception,
            );

            $answer =
                $this->safeFallback(
                    $message,
                );

            $fallbackUsed =
                true;

            $status =
                'failed';

            $errorCode =
                'LOCAL_PROVIDER_FAILED';

            $errorMessage =
                'Local AI provider failed; safe support fallback was returned.';
        }

        $maxOutputCharacters =
            max(
                100,
                min(
                    (int) (
                        $setting
                            ?->max_output_characters
                        ?: config(
                            'karwan_ai.max_output_characters',
                            4000,
                        )
                    ),
                    12000,
                ),
            );

        $reply =
            mb_substr(
                (string) (
                    $answer['reply']
                    ?? ''
                ),
                0,
                $maxOutputCharacters,
            );

        $latencyMs =
            max(
                0,
                (int) round(
                    (
                        hrtime(
                            true,
                        )
                        - $startedAt
                    )
                    / 1_000_000,
                ),
            );

        $result = [
            'reply' =>
                $reply,

            'language' =>
                (string) (
                    $answer['language']
                    ?? 'Roman Urdu'
                ),

            'category' =>
                (string) (
                    $answer['category']
                    ?? 'Support'
                ),

            'needs_human_support' =>
                (bool) (
                    $answer['needs_human_support']
                    ?? false
                ),

            'provider' =>
                $actualProvider,

            'requested_provider' =>
                $requestedProvider,

            'model' =>
                $model
                    ? (string) $model
                    : null,

            'source' =>
                (string) (
                    $answer['source']
                    ?? 'safe_fallback'
                ),

            'fallback_used' =>
                $fallbackUsed,

            'latency_ms' =>
                $latencyMs,

            'request_id' =>
                $requestUuid,
        ];

        $this->recordUsage(
            result: $result,
            message: $message,
            sessionToken: $sessionToken,
            customerId: $customerId,
            status: $status,
            errorCode: $errorCode,
            errorMessage: $errorMessage,
        );

        return $result;
    }

    private function setting(): ?AiSetting
    {
        if (
            ! Schema::hasTable(
                'ai_settings',
            )
        ) {
            return null;
        }

        return AiSetting::current();
    }

    private function recordUsage(
        array $result,
        string $message,
        ?string $sessionToken,
        ?int $customerId,
        string $status,
        ?string $errorCode,
        ?string $errorMessage,
    ): void {
        if (
            ! Schema::hasTable(
                'ai_usage_logs',
            )
        ) {
            return;
        }

        try {
            AiUsageLog::query()->create(
                [
                    'request_uuid' =>
                        $result['request_id'],

                    'session_token' =>
                        $sessionToken,

                    'customer_id' =>
                        $customerId,

                    'requested_provider' =>
                        $result[
                            'requested_provider'
                        ],

                    'provider' =>
                        $result['provider'],

                    'model' =>
                        $result['model'],

                    'source' =>
                        $result['source'],

                    'language' =>
                        $result['language'],

                    'category' =>
                        $result['category'],

                    'status' =>
                        $status,

                    'fallback_used' =>
                        $result[
                            'fallback_used'
                        ],

                    'latency_ms' =>
                        $result['latency_ms'],

                    'input_characters' =>
                        mb_strlen(
                            $message,
                        ),

                    'output_characters' =>
                        mb_strlen(
                            $result['reply'],
                        ),

                    'error_code' =>
                        $errorCode,

                    'error_message' =>
                        $errorMessage
                            ? mb_substr(
                                $errorMessage,
                                0,
                                1000,
                            )
                            : null,

                    'meta' => [
                        'version' =>
                            1,

                        'privacy' =>
                            'lengths_only',

                        'external_request' =>
                            false,
                    ],
                ],
            );
        } catch (Throwable $exception) {
            report(
                $exception,
            );
        }
    }

    private function safeFallback(
        string $message,
    ): array {
        $language =
            $this->detectLanguage(
                $message,
            );

        $reply =
            match ($language) {
                'Urdu' =>
                    'معذرت، کاروان اے آئی ابھی جواب مکمل نہیں کر سکا۔ براہ کرم سپورٹ ٹکٹ یا واٹس ایپ کے ذریعے ہماری ٹیم سے رابطہ کریں۔',

                'English' =>
                    'Sorry, Karwan AI could not complete the answer. Please contact our team through a support ticket or WhatsApp.',

                default =>
                    'Maazrat, Karwan AI abhi jawab complete nahi kar saka. Support ticket ya WhatsApp ke zariye hamari team se rabta karein.',
            };

        return [
            'reply' =>
                $reply,

            'language' =>
                $language,

            'category' =>
                'Support',

            'needs_human_support' =>
                true,

            'source' =>
                'safe_fallback',
        ];
    }

    private function detectLanguage(
        string $message,
    ): string {
        if (
            preg_match(
                '/[\x{0600}-\x{06FF}]/u',
                $message,
            )
        ) {
            return 'Urdu';
        }

        $lower =
            Str::lower(
                $message,
            );

        foreach (
            [
                'what',
                'how',
                'please',
                'need',
                'want',
                'where',
                'when',
                'price',
                'information',
            ]
            as $term
        ) {
            if (
                Str::contains(
                    $lower,
                    $term,
                )
            ) {
                return 'English';
            }
        }

        return 'Roman Urdu';
    }
}
