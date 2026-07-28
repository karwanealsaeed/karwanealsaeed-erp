<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiKnowledgeBase;
use App\Services\KarwanAiKnowledgeService;
use App\Services\KarwanAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class KarwanAiVisualRefinementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_keeps_only_one_compact_ai_floating_option(): void
    {
        config()->set(
            'karwan_ai.enabled',
            true,
        );

        $response =
            $this->get(
                route(
                    'website.home',
                ),
            );

        $response->assertOk();

        $html =
            $response->getContent();

        $previousLibxmlState =
            libxml_use_internal_errors(
                true,
            );

        $document =
            new \DOMDocument;

        $loaded =
            $document->loadHTML(
                $html,
            );

        libxml_clear_errors();

        libxml_use_internal_errors(
            $previousLibxmlState,
        );

        $this->assertTrue(
            $loaded,
        );

        $xpath =
            new \DOMXPath(
                $document,
            );

        $widgetNodes =
            $xpath->query(
                '//*[@data-karwan-ai-widget]',
            );

        $this->assertNotFalse(
            $widgetNodes,
        );

        $this->assertSame(
            1,
            $widgetNodes->length,
        );

        $this->assertStringNotContainsString(
            'class="kes-whatsapp"',
            $html,
        );

        $this->assertStringNotContainsString(
            'class="kes-support-widget"',
            $html,
        );

        $this->assertStringContainsString(
            'class="karwan-ai-icon-svg"',
            $html,
        );

        $this->assertStringNotContainsString(
            '>🤖</span>',
            $html,
        );

        $this->assertSame(
            2,
            substr_count(
                $html,
                'https://wa.me/',
            ),
        );
    }

    public function test_website_layout_connects_visible_phone_numbers_to_whatsapp(): void
    {
        $source =
            file_get_contents(
                resource_path(
                    'views/website/layout.blade.php',
                ),
            );

        $this->assertStringContainsString(
            'href="tel:',
            $source,
        );

        $this->assertStringNotContainsString(
            '<a class="kes-whatsapp"',
            $source,
        );

        $this->assertStringNotContainsString(
            '<div class="kes-support-widget">',
            $source,
        );

        $this->assertGreaterThanOrEqual(
            2,
            substr_count(
                $source,
                'https://wa.me/',
            ),
        );

        $this->assertStringContainsString(
            'target="_blank"',
            $source,
        );

        $this->assertStringContainsString(
            'rel="noopener noreferrer"',
            $source,
        );
    }

    public function test_widget_uses_professional_svg_controls(): void
    {
        $source =
            file_get_contents(
                resource_path(
                    'views/components/karwan-ai-widget.blade.php',
                ),
            );

        foreach (
            [
                'class="karwan-ai-icon-svg"',
                'class="karwan-ai-button-icon"',
                'class="karwan-ai-whatsapp-icon"',
                'viewBox="0 0 24 24"',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }

        $this->assertStringNotContainsString(
            '>🤖</span>',
            $source,
        );
    }

    public function test_compact_widget_css_contract_is_available(): void
    {
        $source =
            file_get_contents(
                public_path(
                    'css/karwan-ai.css',
                ),
            );

        foreach (
            [
                '/* COMPACT AI VISUAL REFINEMENT START */',
                'width: min(332px, calc(100vw - 24px));',
                'min-width: 126px;',
                'min-height: 42px;',
                'max-height: min(520px, calc(100dvh - 82px));',
                'min-height: 150px;',
                'min-height: 58px;',
                '.karwan-ai-footer > span',
                '@media (max-width: 640px)',
                'bottom: calc(10px + env(safe-area-inset-bottom, 0px));',
                '/* COMPACT AI VISUAL REFINEMENT END */',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }
    }

    public function test_knowledge_priority_does_not_match_without_semantic_terms(): void
    {
        AiKnowledgeBase::query()
            ->create([
                'title' => 'Agent Portal Balance',

                'category' => 'Agent Ledger',

                'keywords' => 'balance, ledger, account statement, mera balance, agent balance',

                'answer_english' => 'Registered agents can view their own balance and ledger.',

                'answer_urdu' => 'رجسٹرڈ ایجنٹ اپنا بیلنس اور لیجر دیکھ سکتے ہیں۔',

                'answer_roman_urdu' => 'Registered agent apna balance aur ledger check kar sakta hai.',

                'priority' => 10,

                'status' => 'Active',
            ]);

        $answer =
            app(
                KarwanAiKnowledgeService::class,
            )->answer(
                'kai kia services hn',
            );

        $this->assertNull(
            $answer,
        );
    }

    public function test_general_services_questions_return_complete_company_services(): void
    {
        $service =
            app(
                KarwanAiService::class,
            );

        foreach (
            [
                [
                    'question' => 'kai kia services hn',

                    'umrah_term' => 'Umrah',

                    'visa_term' => 'visa',
                ],
                [
                    'question' => 'what services do you offer',

                    'umrah_term' => 'Umrah',

                    'visa_term' => 'visa',
                ],
                [
                    'question' => 'آپ کون سی سروسز دیتے ہیں',

                    'umrah_term' => 'عمرہ',

                    'visa_term' => 'ویزا',
                ],
            ] as $case
        ) {
            $answer =
                $service->answer(
                    $case[
                        'question'
                    ],
                );

            $this->assertSame(
                'Services',
                $answer[
                    'category'
                ],
            );

            $this->assertStringContainsString(
                $case[
                    'umrah_term'
                ],
                $answer[
                    'reply'
                ],
            );

            $this->assertStringContainsString(
                $case[
                    'visa_term'
                ],
                Str::lower(
                    $answer[
                        'reply'
                    ],
                ),
            );
        }
    }

    public function test_public_chat_services_question_never_returns_agent_ledger(): void
    {
        AiKnowledgeBase::query()
            ->create([
                'title' => 'Agent Portal Balance',

                'category' => 'Agent Ledger',

                'keywords' => 'balance, ledger, account statement, mera balance, agent balance',

                'answer_english' => 'Registered agents can view their own balance and ledger.',

                'answer_urdu' => 'رجسٹرڈ ایجنٹ اپنا بیلنس اور لیجر دیکھ سکتے ہیں۔',

                'answer_roman_urdu' => 'Registered agent apna balance aur ledger Agent Portal se check kar sakta hai.',

                'priority' => 10,

                'status' => 'Active',
            ]);

        $response =
            $this->postJson(
                route(
                    'karwan-ai.chat',
                ),
                [
                    'message' => 'kai kia services hn',
                ],
            );

        $response
            ->assertOk()
            ->assertJsonPath(
                'success',
                true,
            )
            ->assertJsonPath(
                'category',
                'Services',
            )
            ->assertJsonPath(
                'provider',
                'local',
            );

        $reply =
            (string) $response->json(
                'reply',
            );

        $this->assertStringContainsString(
            'Umrah',
            $reply,
        );

        $this->assertStringNotContainsString(
            'ledger',
            Str::lower(
                $reply,
            ),
        );
    }

    public function test_widget_renders_without_company_context(): void
    {
        config()->set(
            'karwan_ai.enabled',
            true,
        );

        $html =
            view(
                'components.karwan-ai-widget',
            )->render();

        $this->assertStringContainsString(
            'data-karwan-ai-widget',
            $html,
        );

        $this->assertStringContainsString(
            'https://wa.me/923012592342',
            $html,
        );

        $source =
            file_get_contents(
                resource_path(
                    'views/components/karwan-ai-widget.blade.php',
                ),
            );

        $this->assertStringContainsString(
            '$company ?? null',
            $source,
        );

        $this->assertStringNotContainsString(
            '$company->',
            $source,
        );
    }
}
