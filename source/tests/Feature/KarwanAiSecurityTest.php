<?php

namespace Tests\Feature;

use App\Filament\Resources\AiChatMessages\AiChatMessageResource;
use App\Filament\Resources\AiKnowledgeBases\AiKnowledgeBaseResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\AiChatMessage;
use App\Models\AiKnowledgeBase;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAiSecurityTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence =
        0;

    public function test_security_configuration_is_available(): void
    {
        $this->assertTrue(
            (bool) config(
                'karwan_ai.enabled',
            ),
        );

        $this->assertSame(
            2000,
            config(
                'karwan_ai.message_max_length',
            ),
        );

        $this->assertSame(
            30,
            config(
                'karwan_ai.history_limit',
            ),
        );

        $this->assertSame(
            '20,1',
            config(
                'karwan_ai.rate_limits.chat',
            ),
        );

        $this->assertNotEmpty(
            config(
                'karwan_ai.session_hmac_key',
            ),
        );
    }

    public function test_ai_permissions_and_role_defaults_are_registered(): void
    {
        $options =
            UserForm::getPermissionOptions();

        $permissions = [
            'ai_chat_messages_view',
            'ai_chat_messages_delete',
            'ai_knowledge_bases_view',
            'ai_knowledge_bases_create',
            'ai_knowledge_bases_edit',
            'ai_knowledge_bases_delete',
        ];

        foreach ($permissions as $permission) {
            $this->assertArrayHasKey(
                $permission,
                $options,
            );

            $this->assertContains(
                $permission,
                UserForm::getDefaultPermissions(
                    'Admin',
                ),
            );
        }

        $manager =
            UserForm::getDefaultPermissions(
                'Manager',
            );

        $this->assertContains(
            'ai_chat_messages_view',
            $manager,
        );

        $this->assertContains(
            'ai_knowledge_bases_view',
            $manager,
        );

        $this->assertContains(
            'ai_knowledge_bases_create',
            $manager,
        );

        $this->assertContains(
            'ai_knowledge_bases_edit',
            $manager,
        );

        $this->assertNotContains(
            'ai_chat_messages_delete',
            $manager,
        );

        $this->assertNotContains(
            'ai_knowledge_bases_delete',
            $manager,
        );
    }

    public function test_admin_ai_resources_require_permissions(): void
    {
        $unauthorized =
            User::factory()->create(
                [
                    'role' =>
                        'Accountant',

                    'is_active' =>
                        true,

                    'permissions' =>
                        [],
                ],
            );

        $this->actingAs(
            $unauthorized,
        )
            ->get(
                route(
                    'filament.admin.resources.ai-chat-messages.index',
                ),
            )
            ->assertForbidden();

        $this->actingAs(
            $unauthorized,
        )
            ->get(
                route(
                    'filament.admin.resources.ai-knowledge-bases.index',
                ),
            )
            ->assertForbidden();

        $viewer =
            User::factory()->create(
                [
                    'role' =>
                        'Accountant',

                    'is_active' =>
                        true,

                    'permissions' => [
                        'ai_chat_messages_view',
                        'ai_knowledge_bases_view',
                    ],
                ],
            );

        $this->actingAs(
            $viewer,
        )
            ->get(
                route(
                    'filament.admin.resources.ai-chat-messages.index',
                ),
            )
            ->assertOk();

        $this->actingAs(
            $viewer,
        )
            ->get(
                route(
                    'filament.admin.resources.ai-knowledge-bases.index',
                ),
            )
            ->assertOk();

        $message =
            AiChatMessage::query()->create(
                [
                    'session_token' =>
                        str_repeat(
                            'a',
                            64,
                        ),

                    'sender' =>
                        'user',

                    'language' =>
                        'English',

                    'message' =>
                        'Permission test',
                ],
            );

        $knowledge =
            AiKnowledgeBase::query()->create(
                [
                    'title' =>
                        'Permission Test',

                    'keywords' =>
                        'permission-test',

                    'answer_english' =>
                        'Permission test answer.',

                    'priority' =>
                        1,

                    'status' =>
                        'Active',
                ],
            );

        $this->assertFalse(
            AiChatMessageResource::canDelete(
                $message,
            ),
        );

        $this->assertFalse(
            AiKnowledgeBaseResource::canEdit(
                $knowledge,
            ),
        );

        $manager =
            User::factory()->create(
                [
                    'role' =>
                        'Manager',

                    'is_active' =>
                        true,

                    'permissions' => [
                        'ai_chat_messages_view',
                        'ai_knowledge_bases_view',
                        'ai_knowledge_bases_edit',
                    ],
                ],
            );

        $this->actingAs(
            $manager,
        );

        $this->assertTrue(
            AiKnowledgeBaseResource::canEdit(
                $knowledge,
            ),
        );

        $this->assertFalse(
            AiKnowledgeBaseResource::canDelete(
                $knowledge,
            ),
        );
    }

    public function test_public_ai_routes_have_individual_rate_limits(): void
    {
        $expected = [
            'karwan-ai.chat' =>
                'throttle:20,1',

            'karwan-ai.history' =>
                'throttle:60,1',

            'karwan-ai.clear' =>
                'throttle:10,1',
        ];

        foreach (
            $expected
            as $routeName => $middleware
        ) {
            $route =
                Route::getRoutes()
                    ->getByName(
                        $routeName,
                    );

            $this->assertNotNull(
                $route,
            );

            $this->assertContains(
                $middleware,
                $route->gatherMiddleware(),
            );
        }
    }

    public function test_emergency_switch_disables_all_public_ai_endpoints(): void
    {
        config()->set(
            'karwan_ai.enabled',
            false,
        );

        $this->postJson(
            route(
                'karwan-ai.chat',
            ),
            [
                'message' =>
                    'Visa information',
            ],
        )
            ->assertStatus(
                503,
            )
            ->assertJson(
                [
                    'success' =>
                        false,

                    'code' =>
                        'AI_DISABLED',
                ],
            );

        $this->getJson(
            route(
                'karwan-ai.history',
            ),
        )
            ->assertStatus(
                503,
            )
            ->assertJson(
                [
                    'code' =>
                        'AI_DISABLED',
                ],
            );

        $this->deleteJson(
            route(
                'karwan-ai.clear',
            ),
        )
            ->assertStatus(
                503,
            )
            ->assertJson(
                [
                    'code' =>
                        'AI_DISABLED',
                ],
            );

        $this->assertDatabaseCount(
            'ai_chat_messages',
            0,
        );
    }

    public function test_chat_rejects_messages_over_configured_limit(): void
    {
        $response =
            $this->postJson(
                route(
                    'karwan-ai.chat',
                ),
                [
                    'message' =>
                        str_repeat(
                            'a',
                            2001,
                        ),
                ],
            );

        $this->assertSame(
            422,
            $response->getStatusCode(),
        );

        $this->assertSame(
            'Message is too long.',
            data_get(
                $response->json(),
                'errors.message.0',
            ),
        );

        $this->assertDatabaseCount(
            'ai_chat_messages',
            0,
        );
    }

    public function test_chat_stores_transactional_pair_and_prefers_knowledge_base(): void
    {
        AiKnowledgeBase::query()->create(
            [
                'title' =>
                    'Security Test Knowledge',

                'category' =>
                    'Visa',

                'keywords' =>
                    'special-ai-security-keyword',

                'answer_english' =>
                    'Secure knowledge-base answer.',

                'answer_roman_urdu' =>
                    'Secure knowledge-base answer.',

                'priority' =>
                    10,

                'status' =>
                    'Active',
            ],
        );

        $response =
            $this->postJson(
                route(
                    'karwan-ai.chat',
                ),
                [
                    'message' =>
                        'special-ai-security-keyword',
                ],
            );

        $response
            ->assertOk()
            ->assertJson(
                [
                    'success' =>
                        true,

                    'reply' =>
                        'Secure knowledge-base answer.',

                    'source' =>
                        'knowledge_base',
                ],
            );

        $messages =
            AiChatMessage::query()
                ->orderBy(
                    'id',
                )
                ->get();

        $this->assertCount(
            2,
            $messages,
        );

        $this->assertSame(
            'user',
            $messages[0]->sender,
        );

        $this->assertSame(
            'assistant',
            $messages[1]->sender,
        );

        $this->assertSame(
            $messages[0]->session_token,
            $messages[1]->session_token,
        );

        $this->assertSame(
            64,
            strlen(
                $messages[0]->session_token,
            ),
        );

        $this->assertSame(
            'knowledge_base',
            $messages[1]->meta['source']
            ?? null,
        );
    }

    public function test_history_and_clear_are_session_scoped(): void
    {
        $sessionSeed =
            str_repeat(
                's',
                64,
            );

        $sessionState = [
            'karwan_ai_session_seed' =>
                $sessionSeed,
        ];

        $this->withSession(
            $sessionState,
        )
            ->postJson(
                route(
                    'karwan-ai.chat',
                ),
                [
                    'message' =>
                        'Visa information',
                ],
            )
            ->assertOk();

        $ownMessage =
            AiChatMessage::query()
                ->where(
                    'sender',
                    'user',
                )
                ->firstOrFail();

        AiChatMessage::query()->create(
            [
                'session_token' =>
                    str_repeat(
                        'f',
                        64,
                    ),

                'sender' =>
                    'user',

                'language' =>
                    'English',

                'message' =>
                    'Foreign session message',
            ],
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
            )
            ->assertJsonMissing(
                [
                    'message' =>
                        'Foreign session message',
                ],
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
            ->assertJson(
                [
                    'success' =>
                        true,

                    'deleted_messages' =>
                        2,
                ],
            );

        $this->assertDatabaseMissing(
            'ai_chat_messages',
            [
                'session_token' =>
                    $ownMessage->session_token,
            ],
        );

        $this->assertDatabaseHas(
            'ai_chat_messages',
            [
                'session_token' =>
                    str_repeat(
                        'f',
                        64,
                    ),

                'message' =>
                    'Foreign session message',
            ],
        );
    }

    public function test_history_is_also_customer_scoped(): void
    {
        $firstCustomer =
            $this->customer();

        $secondCustomer =
            $this->customer();

        $sessionSeed =
            str_repeat(
                'c',
                64,
            );

        $customerSession = [
            'customer_portal_id' =>
                $firstCustomer->id,

            'karwan_ai_session_seed' =>
                $sessionSeed,
        ];

        $this->withSession(
            $customerSession,
        )
            ->postJson(
                route(
                    'karwan-ai.chat',
                ),
                [
                    'message' =>
                        'Umrah package information',
                ],
            )
            ->assertOk();

        $ownMessage =
            AiChatMessage::query()
                ->where(
                    'customer_id',
                    $firstCustomer->id,
                )
                ->firstOrFail();

        AiChatMessage::query()->create(
            [
                'session_token' =>
                    $ownMessage->session_token,

                'customer_id' =>
                    $secondCustomer->id,

                'sender' =>
                    'user',

                'language' =>
                    'English',

                'message' =>
                    'Other customer private message',
            ],
        );

        $this->withSession(
            $customerSession,
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
            )
            ->assertJsonMissing(
                [
                    'message' =>
                        'Other customer private message',
                ],
            );

        $this->withSession(
            $customerSession,
        )
            ->deleteJson(
                route(
                    'karwan-ai.clear',
                ),
            )
            ->assertOk()
            ->assertJson(
                [
                    'success' =>
                        true,

                    'deleted_messages' =>
                        2,
                ],
            );

        $this->assertDatabaseHas(
            'ai_chat_messages',
            [
                'customer_id' =>
                    $secondCustomer->id,

                'message' =>
                    'Other customer private message',
            ],
        );

        $this->assertDatabaseMissing(
            'ai_chat_messages',
            [
                'customer_id' =>
                    $firstCustomer->id,
            ],
        );
    }

    public function test_controller_source_contains_security_controls(): void
    {
        $source =
            file_get_contents(
                app_path(
                    'Http/Controllers/KarwanAiController.php',
                ),
            );

        $this->assertStringContainsString(
            'hash_hmac(',
            $source,
        );

        $this->assertStringContainsString(
            "'karwan_ai_session_seed'",
            $source,
        );

        $this->assertStringContainsString(
            'Str::random(',
            $source,
        );

        $this->assertStringContainsString(
            'DB::transaction(',
            $source,
        );

        $this->assertStringContainsString(
            'ownedMessagesQuery(',
            $source,
        );

        $this->assertStringContainsString(
            'whereNull(',
            $source,
        );

        $this->assertStringContainsString(
            "'AI_DISABLED'",
            $source,
        );
    }

    private function customer(): Customer
    {
        self::$sequence++;

        $sequence =
            self::$sequence;

        return Customer::query()->create(
            [
                'name' =>
                    "Karwan AI Customer {$sequence}",

                'mobile' =>
                    '0312'
                    .str_pad(
                        (string) $sequence,
                        7,
                        '0',
                        STR_PAD_LEFT,
                    ),

                'email' =>
                    "karwan-ai-{$sequence}@example.test",

                'customer_type' =>
                    'Agent',

                'status' =>
                    'Active',

                'portal_password' =>
                    Hash::make(
                        'PortalPassword123!',
                    ),

                'portal_registered_at' =>
                    now(
                        'Asia/Karachi',
                    ),
            ],
        );
    }
}
