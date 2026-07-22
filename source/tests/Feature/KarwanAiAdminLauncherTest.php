<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class KarwanAiAdminLauncherTest extends TestCase
{
    public function test_admin_ai_render_hook_provider_is_registered(): void
    {
        $providers = file_get_contents(base_path('bootstrap/providers.php'));
        $provider = file_get_contents(
            app_path('Providers/KarwanAiAdminUiServiceProvider.php'),
        );

        $this->assertIsString($providers);
        $this->assertIsString($provider);
        $this->assertStringContainsString(
            'KarwanAiAdminUiServiceProvider::class',
            $providers,
        );
        $this->assertStringContainsString(
            'PanelsRenderHook::BODY_END',
            $provider,
        );
        $this->assertStringContainsString(
            "view('filament.admin.karwan-ai-launcher')",
            $provider,
        );
    }

    public function test_admin_launcher_is_limited_to_admin_panel_and_public_information_mode(): void
    {
        $source = file_get_contents(
            resource_path('views/filament/admin/karwan-ai-launcher.blade.php'),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            "getCurrentPanel()?->getId() === 'admin'",
            $source,
        );
        $this->assertStringContainsString(
            'data-karwan-ai-admin-mode="public-information-only"',
            $source,
        );
        $this->assertStringContainsString(
            "@include('components.karwan-ai-widget')",
            $source,
        );
    }

    public function test_admin_launcher_loads_widget_assets_once(): void
    {
        $source = file_get_contents(
            resource_path('views/filament/admin/karwan-ai-launcher.blade.php'),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString('@once', $source);
        $this->assertStringContainsString(
            "asset('css/karwan-ai.css')",
            $source,
        );
        $this->assertStringContainsString(
            "asset('js/karwan-ai.js')",
            $source,
        );
    }

    public function test_launcher_css_is_compact_for_public_and_admin(): void
    {
        $source = file_get_contents(
            public_path('css/karwan-ai.css'),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'PHASE 8A5R2 COMPACT LAUNCHER START',
            $source,
        );
        $this->assertStringContainsString(
            'width: 44px !important;',
            $source,
        );
        $this->assertStringContainsString(
            '.karwan-ai-admin-host',
            $source,
        );
        $this->assertStringContainsString(
            'width: 40px !important;',
            $source,
        );
    }

    public function test_passport_alert_migration_is_independent_of_optional_column_order(): void
    {
        $path = database_path(
            'migrations/2026_07_20_190712_add_passport_alert_tracking_to_visa_applications.php',
        );

        $source = file_get_contents($path);

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'passport_alert_fingerprint',
            $source,
        );
        $this->assertStringNotContainsString(
            'passport_validity_checked_at',
            $source,
        );
        $this->assertStringContainsString(
            "'passport_alert_sent_at'",
            $source,
        );
        $this->assertStringContainsString(
            "'passport_alert_fingerprint',",
            $source,
        );
    }
}
