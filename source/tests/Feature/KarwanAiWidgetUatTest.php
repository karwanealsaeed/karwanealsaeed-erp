<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KarwanAiWidgetUatTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_renders_accessible_widget_when_enabled(): void
    {
        config()->set(
            'karwan_ai.enabled',
            true,
        );

        $this->get(
            route(
                'website.home',
            ),
        )
            ->assertOk()
            ->assertSee(
                'data-karwan-ai-widget',
                false,
            )
            ->assertSee(
                'role="log"',
                false,
            )
            ->assertSee(
                'aria-live="polite"',
                false,
            )
            ->assertSee(
                'aria-describedby="karwanAiHelp karwanAiCharacterCount"',
                false,
            )
            ->assertSee(
                'rel="noopener noreferrer"',
                false,
            );
    }

    public function test_emergency_switch_removes_widget_markup(): void
    {
        config()->set(
            'karwan_ai.enabled',
            false,
        );

        $this->get(
            route(
                'website.home',
            ),
        )
            ->assertOk()
            ->assertDontSee(
                'data-karwan-ai-widget',
                false,
            )
            ->assertDontSee(
                'id="karwanAiForm"',
                false,
            );
    }

    public function test_widget_source_has_accessibility_and_keyboard_contract(): void
    {
        $source =
            file_get_contents(
                resource_path(
                    'views/components/karwan-ai-widget.blade.php',
                ),
            );

        foreach (
            [
                'aria-controls="karwanAiPanel"',
                'aria-expanded="false"',
                'role="region"',
                'role="status"',
                'role="log"',
                'aria-relevant="additions text"',
                'aria-busy="false"',
                'tabindex="0"',
                'karwan-ai-sr-only',
                'enterkeyhint="send"',
                'event.isComposing',
                'form.requestSubmit()',
                "details.addEventListener(",
                "'toggle'",
            ]
            as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }
    }

    public function test_widget_source_uses_safe_dom_and_network_controls(): void
    {
        $source =
            file_get_contents(
                resource_path(
                    'views/components/karwan-ai-widget.blade.php',
                ),
            );

        foreach (
            [
                'textContent',
                'replaceChildren()',
                'new AbortController()',
                "credentials:\n                                            'same-origin'",
                "cache:\n                                            'no-store'",
                "'X-Requested-With':",
                "'XMLHttpRequest'",
                'widget.dataset.karwanAiReady',
                "navigator.onLine === false",
                "window.addEventListener(\n                    'offline'",
            ]
            as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }

        foreach (
            [
                'innerHTML',
                'insertAdjacentHTML',
                'eval(',
                'localStorage',
                'sessionStorage',
                'document.write(',
            ]
            as $forbiddenMarker
        ) {
            $this->assertStringNotContainsString(
                $forbiddenMarker,
                $source,
            );
        }
    }

    public function test_widget_handles_validation_rate_limit_and_service_outage(): void
    {
        $source =
            file_get_contents(
                resource_path(
                    'views/components/karwan-ai-widget.blade.php',
                ),
            );

        foreach (
            [
                'response.status === 422',
                'response.status === 429',
                'response.status === 503',
                'data.errors.message',
                "error.name === 'AbortError'",
                'labels.rateLimited',
                'labels.unavailable',
                'labels.timeout',
            ]
            as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }
    }

    public function test_widget_styles_are_mobile_safe_and_accessible(): void
    {
        $source =
            file_get_contents(
                public_path(
                    'css/karwan-ai.css',
                ),
            );

        foreach (
            [
                '@media (max-width: 640px)',
                '@media (max-width: 380px)',
                '@media (prefers-reduced-motion: reduce)',
                '@media (forced-colors: active)',
                ':focus-visible',
                'env(safe-area-inset-bottom',
                '100dvh',
                'min-height: 44px',
                'overscroll-behavior: contain',
                'touch-action',
            ]
            as $marker
        ) {
            if (
                $marker === 'touch-action'
            ) {
                $this->assertStringContainsString(
                    '-webkit-tap-highlight-color',
                    $source,
                );

                continue;
            }

            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }
    }

    public function test_public_and_portal_layouts_load_widget_assets_once(): void
    {
        foreach (
            [
                resource_path(
                    'views/website/layout.blade.php',
                ),
                resource_path(
                    'views/customer-portal/layout.blade.php',
                ),
            ]
            as $layoutPath
        ) {
            $source =
                file_get_contents(
                    $layoutPath,
                );

            $this->assertSame(
                1,
                substr_count(
                    $source,
                    "asset('css/karwan-ai.css')",
                ),
            );

            $this->assertSame(
                1,
                substr_count(
                    $source,
                    "@include('components.karwan-ai-widget')",
                ),
            );
        }
    }

    public function test_public_chat_history_and_clear_complete_browser_style_flow(): void
    {
        $sessionState = [
            'karwan_ai_session_seed' =>
                str_repeat(
                    'u',
                    64,
                ),
        ];

        $chatResponse =
            $this->withSession(
                $sessionState,
            )
                ->postJson(
                    route(
                        'karwan-ai.chat',
                    ),
                    [
                        'message' =>
                            'Assalam o Alaikum',
                    ],
                );

        $chatResponse
            ->assertOk()
            ->assertJsonPath(
                'success',
                true,
            )
            ->assertJsonPath(
                'provider',
                'local',
            )
            ->assertJsonStructure(
                [
                    'reply',
                    'source',
                    'provider',
                    'requested_provider',
                    'fallback_used',
                    'latency_ms',
                    'request_id',
                ],
            );

        $requestId =
            (string) $chatResponse->json(
                'request_id',
            );

        $this->assertNotSame(
            '',
            $requestId,
        );

        $this->withSession(
            $sessionState,
        )
            ->getJson(
                route(
                    'karwan-ai.history',
                ),
            )
            ->assertOk()
            ->assertJsonCount(
                2,
                'messages',
            );

        $this->withSession(
            $sessionState,
        )
            ->deleteJson(
                route(
                    'karwan-ai.clear',
                ),
            )
            ->assertOk()
            ->assertJsonPath(
                'success',
                true,
            )
            ->assertJsonPath(
                'deleted_messages',
                2,
            );

        $this->assertDatabaseCount(
            'ai_chat_messages',
            0,
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
    }

    public function test_uat_document_covers_manual_browser_sign_off(): void
    {
        $source =
            file_get_contents(
                base_path(
                    'docs/karwan-ai-final-uat.md',
                ),
            );

        foreach (
            [
                'Chrome / Edge desktop',
                'Mobile viewport',
                'Keyboard-only',
                'Screen reader',
                'Offline / timeout',
                'Rate limit',
                'Emergency switch',
                'Manual sign-off',
            ]
            as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }
    }
}
