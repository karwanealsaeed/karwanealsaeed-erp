<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiKnowledgeBase;
use App\Models\AiSetting;
use App\Models\AiUsageLog;
use App\Services\KarwanAi\LocalKarwanAiProvider;
use App\Services\KarwanAiManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class KarwanAiProviderUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_settings_and_usage_tables_are_private_and_ready(): void
    {
        $this->assertTrue(
            Schema::hasTable(
                'ai_settings',
            ),
        );

        $this->assertTrue(
            Schema::hasTable(
                'ai_usage_logs',
            ),
        );

        foreach (
            [
                'provider',
                'external_enabled',
                'fallback_enabled',
                'timeout_seconds',
                'max_output_characters',
                'options',
            ]
            as $column
        ) {
            $this->assertTrue(
                Schema::hasColumn(
                    'ai_settings',
                    $column,
                ),
            );
        }

        foreach (
            [
                'request_uuid',
                'requested_provider',
                'provider',
                'source',
                'status',
                'fallback_used',
                'latency_ms',
                'input_characters',
                'output_characters',
                'error_code',
                'meta',
            ]
            as $column
        ) {
            $this->assertTrue(
                Schema::hasColumn(
                    'ai_usage_logs',
                    $column,
                ),
            );
        }

        foreach (
            [
                'api_key',
                'secret',
                'access_token',
                'credential',
                'prompt',
                'message',
                'reply',
                'response',
            ]
            as $forbiddenColumn
        ) {
            $this->assertFalse(
                Schema::hasColumn(
                    'ai_settings',
                    $forbiddenColumn,
                ),
            );

            $this->assertFalse(
                Schema::hasColumn(
                    'ai_usage_logs',
                    $forbiddenColumn,
                ),
            );
        }
    }

    public function test_default_setting_is_local_and_external_ai_is_disabled(): void
    {
        $setting =
            AiSetting::current();

        $this->assertSame(
            'local',
            $setting->provider,
        );

        $this->assertFalse(
            $setting->external_enabled,
        );

        $this->assertTrue(
            $setting->fallback_enabled,
        );

        $this->assertSame(
            10,
            $setting->timeout_seconds,
        );

        $this->assertSame(
            4000,
            $setting->max_output_characters,
        );

        $this->assertDatabaseCount(
            'ai_settings',
            1,
        );

        $this->assertFalse(
            (bool) config(
                'karwan_ai.external_enabled',
            ),
        );
    }

    public function test_manager_prefers_knowledge_base_and_tracks_lengths_only(): void
    {
        AiKnowledgeBase::query()->create(
            [
                'title' =>
                    'Phase 8A3 Provider Test',

                'category' =>
                    'Visa',

                'keywords' =>
                    'phase8a3-provider-keyword',

                'answer_english' =>
                    'Provider knowledge answer.',

                'answer_roman_urdu' =>
                    'Provider knowledge answer.',

                'priority' =>
                    10,

                'status' =>
                    'Active',
            ],
        );

        $result =
            app(
                KarwanAiManager::class,
            )->answer(
                message:
                    'phase8a3-provider-keyword',

                sessionToken:
                    str_repeat(
                        'a',
                        64,
                    ),
            );

        $this->assertSame(
            'local',
            $result['provider'],
        );

        $this->assertSame(
            'knowledge_base',
            $result['source'],
        );

        $this->assertSame(
            'Provider knowledge answer.',
            $result['reply'],
        );

        $log =
            AiUsageLog::query()
                ->firstOrFail();

        $this->assertSame(
            mb_strlen(
                'phase8a3-provider-keyword',
            ),
            $log->input_characters,
        );

        $this->assertSame(
            mb_strlen(
                'Provider knowledge answer.',
            ),
            $log->output_characters,
        );

        $this->assertSame(
            'lengths_only',
            $log->meta['privacy']
            ?? null,
        );

        $this->assertFalse(
            array_key_exists(
                'message',
                $log->getAttributes(),
            ),
        );

        $this->assertFalse(
            array_key_exists(
                'reply',
                $log->getAttributes(),
            ),
        );
    }

    public function test_local_rules_are_used_when_knowledge_does_not_match(): void
    {
        $result =
            app(
                KarwanAiManager::class,
            )->answer(
                'Assalam o Alaikum',
            );

        $this->assertSame(
            'local',
            $result['provider'],
        );

        $this->assertSame(
            'local_rules',
            $result['source'],
        );

        $this->assertNotSame(
            '',
            $result['reply'],
        );

        $this->assertDatabaseHas(
            'ai_usage_logs',
            [
                'provider' =>
                    'local',

                'source' =>
                    'local_rules',

                'status' =>
                    'success',
            ],
        );
    }

    public function test_disabled_external_provider_falls_back_to_local(): void
    {
        $setting =
            AiSetting::current();

        $setting->update(
            [
                'provider' =>
                    'openai',

                'external_enabled' =>
                    false,
            ],
        );

        $result =
            app(
                KarwanAiManager::class,
            )->answer(
                'Visa information please',
            );

        $this->assertSame(
            'openai',
            $result[
                'requested_provider'
            ],
        );

        $this->assertSame(
            'local',
            $result['provider'],
        );

        $this->assertTrue(
            $result['fallback_used'],
        );

        $this->assertDatabaseHas(
            'ai_usage_logs',
            [
                'requested_provider' =>
                    'openai',

                'provider' =>
                    'local',

                'status' =>
                    'fallback',

                'fallback_used' =>
                    true,

                'error_code' =>
                    'EXTERNAL_PROVIDER_DISABLED',
            ],
        );
    }

    public function test_public_chat_response_contains_provider_metadata_and_usage_log(): void
    {
        $response =
            $this->postJson(
                route(
                    'karwan-ai.chat',
                ),
                [
                    'message' =>
                        'Umrah package information',
                ],
            );

        $response
            ->assertOk()
            ->assertJsonPath(
                'provider',
                'local',
            )
            ->assertJsonPath(
                'requested_provider',
                'local',
            )
            ->assertJsonStructure(
                [
                    'success',
                    'reply',
                    'language',
                    'category',
                    'needs_human_support',
                    'source',
                    'provider',
                    'requested_provider',
                    'model',
                    'fallback_used',
                    'latency_ms',
                    'request_id',
                ],
            );

        $requestId =
            (string) $response->json(
                'request_id',
            );

        $this->assertNotSame(
            '',
            $requestId,
        );

        $this->assertDatabaseHas(
            'ai_usage_logs',
            [
                'request_uuid' =>
                    $requestId,

                'provider' =>
                    'local',
            ],
        );

        $this->assertDatabaseCount(
            'ai_chat_messages',
            2,
        );
    }

    public function test_provider_failure_returns_safe_fallback_and_records_failure(): void
    {
        $this->mock(
            LocalKarwanAiProvider::class,
            function (
                MockInterface $mock,
            ): void {
                $mock->shouldReceive(
                    'name',
                )
                    ->once()
                    ->andReturn(
                        'local',
                    );

                $mock->shouldReceive(
                    'answer',
                )
                    ->once()
                    ->andThrow(
                        new RuntimeException(
                            'Provider secret failure text.',
                        ),
                    );
            },
        );

        $response =
            $this->postJson(
                route(
                    'karwan-ai.chat',
                ),
                [
                    'message' =>
                        'Need urgent help',
                ],
            );

        $response
            ->assertOk()
            ->assertJsonPath(
                'source',
                'safe_fallback',
            )
            ->assertJsonPath(
                'needs_human_support',
                true,
            )
            ->assertJsonPath(
                'fallback_used',
                true,
            );

        $log =
            AiUsageLog::query()
                ->firstOrFail();

        $this->assertSame(
            'failed',
            $log->status,
        );

        $this->assertSame(
            'LOCAL_PROVIDER_FAILED',
            $log->error_code,
        );

        $this->assertStringNotContainsString(
            'Provider secret failure text.',
            (string) $log->error_message,
        );
    }

    public function test_output_is_capped_by_database_setting(): void
    {
        AiSetting::current()->update(
            [
                'max_output_characters' =>
                    100,
            ],
        );

        $this->mock(
            LocalKarwanAiProvider::class,
            function (
                MockInterface $mock,
            ): void {
                $mock->shouldReceive(
                    'name',
                )
                    ->once()
                    ->andReturn(
                        'local',
                    );

                $mock->shouldReceive(
                    'answer',
                )
                    ->once()
                    ->andReturn(
                        [
                            'reply' =>
                                str_repeat(
                                    'x',
                                    500,
                                ),

                            'language' =>
                                'English',

                            'category' =>
                                'Support',

                            'needs_human_support' =>
                                false,

                            'source' =>
                                'local_rules',

                            'fallback_used' =>
                                false,

                            'error_code' =>
                                null,

                            'error_message' =>
                                null,
                        ],
                    );
            },
        );

        $result =
            app(
                KarwanAiManager::class,
            )->answer(
                'Please provide information',
            );

        $this->assertSame(
            100,
            mb_strlen(
                $result['reply'],
            ),
        );

        $this->assertDatabaseHas(
            'ai_usage_logs',
            [
                'output_characters' =>
                    100,
            ],
        );
    }
}
