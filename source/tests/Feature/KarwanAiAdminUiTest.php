<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\AiSettings\AiSettingResource;
use App\Filament\Resources\AiUsageLogs\AiUsageLogResource;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Widgets\AiUsageOverview;
use App\Models\AiSetting;
use App\Models\AiUsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class KarwanAiAdminUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_settings_and_usage_routes_are_registered_read_only(): void
    {
        foreach (
            [
                'filament.admin.resources.ai-settings.index',
                'filament.admin.resources.ai-settings.view',
                'filament.admin.resources.ai-settings.edit',
                'filament.admin.resources.ai-usage-logs.index',
                'filament.admin.resources.ai-usage-logs.view',
            ]
            as $routeName
        ) {
            $this->assertTrue(
                Route::has(
                    $routeName,
                ),
                "Missing route: {$routeName}",
            );
        }

        $this->assertFalse(
            Route::has(
                'filament.admin.resources.ai-settings.create',
            ),
        );

        $this->assertFalse(
            Route::has(
                'filament.admin.resources.ai-usage-logs.create',
            ),
        );

        $this->assertFalse(
            Route::has(
                'filament.admin.resources.ai-usage-logs.edit',
            ),
        );
    }

    public function test_ai_admin_permissions_and_role_defaults_are_registered(): void
    {
        $options =
            UserForm::getPermissionOptions();

        foreach (
            [
                'ai_settings_view',
                'ai_settings_edit',
                'ai_usage_logs_view',
            ]
            as $permission
        ) {
            $this->assertArrayHasKey(
                $permission,
                $options,
            );
        }

        $adminPermissions =
            UserForm::getDefaultPermissions(
                'Admin',
            );

        $managerPermissions =
            UserForm::getDefaultPermissions(
                'Manager',
            );

        foreach (
            [
                'ai_settings_view',
                'ai_settings_edit',
                'ai_usage_logs_view',
            ]
            as $permission
        ) {
            $this->assertContains(
                $permission,
                $adminPermissions,
            );
        }

        $this->assertContains(
            'ai_settings_view',
            $managerPermissions,
        );

        $this->assertContains(
            'ai_usage_logs_view',
            $managerPermissions,
        );

        $this->assertNotContains(
            'ai_settings_edit',
            $managerPermissions,
        );
    }

    public function test_ai_admin_resources_require_explicit_permissions(): void
    {
        $user =
            $this->user();

        $this->actingAs(
            $user,
        );

        $this->assertFalse(
            AiSettingResource::canAccess(),
        );

        $this->assertFalse(
            AiUsageLogResource::canAccess(),
        );

        $user->update(
            [
                'permissions' => [
                    'ai_settings_view',
                    'ai_usage_logs_view',
                ],
            ],
        );

        $user->refresh();

        $this->assertTrue(
            AiSettingResource::canAccess(),
        );

        $this->assertTrue(
            AiUsageLogResource::canAccess(),
        );

        $this->assertFalse(
            AiSettingResource::canEdit(
                AiSetting::current(),
            ),
        );
    }

    public function test_admin_can_render_ai_settings_pages(): void
    {
        $admin =
            $this->user(
                role:
                    'Admin',
            );

        $setting =
            AiSetting::current();

        $this->actingAs(
            $admin,
        )
            ->get(
                route(
                    'filament.admin.resources.ai-settings.index',
                ),
            )
            ->assertOk();

        $this->get(
            route(
                'filament.admin.resources.ai-settings.view',
                $setting,
            ),
        )->assertOk();

        $this->get(
            route(
                'filament.admin.resources.ai-settings.edit',
                $setting,
            ),
        )->assertOk();
    }

    public function test_manager_can_view_settings_but_cannot_edit_them(): void
    {
        $manager =
            $this->user(
                permissions: [
                    'ai_settings_view',
                    'ai_usage_logs_view',
                ],
            );

        $setting =
            AiSetting::current();

        $this->actingAs(
            $manager,
        )
            ->get(
                route(
                    'filament.admin.resources.ai-settings.view',
                    $setting,
                ),
            )
            ->assertOk();

        $this->get(
            route(
                'filament.admin.resources.ai-settings.edit',
                $setting,
            ),
        )->assertForbidden();
    }

    public function test_usage_logs_are_read_only_and_admin_pages_render(): void
    {
        $admin =
            $this->user(
                role:
                    'Admin',
            );

        $log =
            AiUsageLog::query()->create(
                [
                    'request_uuid' =>
                        '11111111-1111-4111-8111-111111111111',

                    'requested_provider' =>
                        'local',

                    'provider' =>
                        'local',

                    'source' =>
                        'knowledge_base',

                    'language' =>
                        'English',

                    'category' =>
                        'Visa',

                    'status' =>
                        'success',

                    'fallback_used' =>
                        false,

                    'latency_ms' =>
                        25,

                    'input_characters' =>
                        18,

                    'output_characters' =>
                        120,

                    'meta' => [
                        'privacy' =>
                            'lengths_only',
                    ],
                ],
            );

        $this->actingAs(
            $admin,
        )
            ->get(
                route(
                    'filament.admin.resources.ai-usage-logs.index',
                ),
            )
            ->assertOk();

        $this->get(
            route(
                'filament.admin.resources.ai-usage-logs.view',
                $log,
            ),
        )->assertOk();

        $this->assertFalse(
            AiUsageLogResource::canCreate(),
        );

        $this->assertFalse(
            AiUsageLogResource::canEdit(
                $log,
            ),
        );

        $this->assertFalse(
            AiUsageLogResource::canDelete(
                $log,
            ),
        );
    }

    public function test_usage_overview_widget_renders_for_authorized_admin(): void
    {
        $admin =
            $this->user(
                role:
                    'Admin',
            );

        AiUsageLog::query()->create(
            [
                'request_uuid' =>
                    '22222222-2222-4222-8222-222222222222',

                'requested_provider' =>
                    'openai',

                'provider' =>
                    'local',

                'source' =>
                    'local_rules',

                'status' =>
                    'fallback',

                'fallback_used' =>
                    true,

                'latency_ms' =>
                    40,

                'input_characters' =>
                    20,

                'output_characters' =>
                    100,
            ],
        );

        $this->actingAs(
            $admin,
        );

        Livewire::test(
            AiUsageOverview::class,
        )->assertOk();
    }

    public function test_ai_admin_ui_source_keeps_external_provider_locked_and_usage_private(): void
    {
        $settingForm =
            file_get_contents(
                app_path(
                    'Filament/Resources/AiSettings/Schemas/AiSettingForm.php',
                ),
            );

        $editPage =
            file_get_contents(
                app_path(
                    'Filament/Resources/AiSettings/Pages/EditAiSetting.php',
                ),
            );

        $usageUi =
            file_get_contents(
                app_path(
                    'Filament/Resources/AiUsageLogs/Tables/AiUsageLogsTable.php',
                ),
            )
            .file_get_contents(
                app_path(
                    'Filament/Resources/AiUsageLogs/Schemas/AiUsageLogInfolist.php',
                ),
            );

        $this->assertStringContainsString(
            "'local' =>",
            $settingForm,
        );

        $this->assertStringNotContainsString(
            "'openai' =>",
            $settingForm,
        );

        $this->assertStringNotContainsString(
            "'gemini' =>",
            $settingForm,
        );

        $this->assertStringContainsString(
            "\$data['external_enabled'] =",
            $editPage,
        );

        $this->assertStringContainsString(
            'false;',
            $editPage,
        );

        foreach (
            [
                "::make('session_token')",
                "::make('message')",
                "::make('reply')",
            ]
            as $forbiddenField
        ) {
            $this->assertStringNotContainsString(
                $forbiddenField,
                $usageUi,
            );
        }
    }

    private function user(
        string $role = 'Manager',
        array $permissions = [],
    ): User {
        return User::factory()->create(
            [
                'role' =>
                    $role,

                'is_active' =>
                    true,

                'permissions' =>
                    $permissions,
            ],
        );
    }
}
