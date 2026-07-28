<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAdminPortalDashboardV3Test extends TestCase
{
    private const ADMIN_ROUTE_HASH =
        'd1fe643943b269ecd2dba5cd2ce5a48c474bd9b20b4878575b724e632860f20d';

    private const ADMIN_LEGACY_THEME_HASH =
        '635cbca6b1ebe9daf418e81182ef388f57defd33f47d0e8ca32b49bb16b78e14';

    private const SHARED_UI_HASH =
        'f1b713b78ce43cc276d6c025131eeef52354e75e6e385fb0722c1b14d8332c33';

    private const ADMIN_THEME_HEAD_HASH =
        '87e7aa5d5a8598cc093a86799ee11909e971a5c5701208ee858b851c05588e98';

    private const ADMIN_PANEL_PROVIDER_HASH =
        '10a9ed77cf8a86ee1141ba518e58f0456caf0feeb9c8ab6400734ba328629471';

    private const AI_USAGE_WIDGET_HASH =
        '02fa071ae908b44895da03716a363dea75ece96c5951a1608a43923c3e4722e4';

    private const DASHBOARD_STATS_HASH =
        'cc11b7ee22bc2b894bcd6ef0ad0f0bb286fa49e636c0ac771a065d3f71c62e73';

    private const DOCUMENT_EXPIRY_STATS_HASH =
        '0c34702dbd8a45cc0a1edbd6132ed382b657e3638421080c59f7cf767e17a8d1';

    public function test_admin_dashboard_navigation_v3_css_contract_exists(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16F-2A ADMIN DASHBOARD AND NAVIGATION V3 START',
                'PHASE 16F-2A ADMIN DASHBOARD AND NAVIGATION V3 END',
                'body.fi-body .fi-layout',
                'body.fi-body .fi-header',
                'body.fi-body .fi-sidebar-group',
                'body.fi-body .fi-sidebar-item-button',
                'body.fi-body .fi-sidebar-close-overlay',
                'body.fi-body .fi-topbar',
                'body.fi-body .fi-global-search',
                'body.fi-body .fi-dropdown-panel',
                'body.fi-body .fi-avatar',
                'body.fi-body .fi-wi-stats-overview-stat',
                'body.fi-body .fi-wi-table',
                'body.fi-body .fi-empty-state',
                '.karwan-ai-admin-host',
                '@media (max-width: 1024px)',
                '@media (max-width: 760px)',
                '@media (max-width: 390px)',
                'html[dir="rtl"] body.fi-body',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }

        $this->assertSame(
            1,
            substr_count(
                $css,
                'PHASE 16F-2A ADMIN DASHBOARD AND NAVIGATION V3 START',
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $css,
                'PHASE 16F-2A ADMIN DASHBOARD AND NAVIGATION V3 END',
            ),
        );
    }

    public function test_admin_dashboard_uses_public_v3_visual_language(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'var(--display)',
                'var(--body)',
                'var(--gold)',
                'var(--green)',
                'var(--green-2)',
                'var(--surface)',
                'var(--surface-2)',
                'var(--line)',
                'var(--shadow-lg)',
                'var(--shadow-md)',
                'var(--shadow-sm)',
                'linear-gradient(',
                'radial-gradient(',
            ] as $token
        ) {
            $this->assertStringContainsString(
                $token,
                $css,
            );
        }
    }

    public function test_admin_and_agent_route_contracts_are_exact(): void
    {
        $routes = Route::getRoutes();
        $adminNames = [];
        $agentNames = [];

        foreach ($routes as $route) {
            $name = (string) ($route->getName() ?? '');

            if (str_starts_with($name, 'filament.admin.')) {
                $adminNames[] = $name;
            }

            if (str_starts_with($name, 'customer.portal.')) {
                $agentNames[] = $name;
            }
        }

        $adminNames =
            array_values(array_unique($adminNames));
        sort($adminNames);

        $json = json_encode(
            $adminNames,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE,
        );

        $this->assertIsString($json);
        $this->assertCount(430, $routes);
        $this->assertCount(313, $adminNames);
        $this->assertCount(36, array_unique($agentNames));
        $this->assertSame(
            self::ADMIN_ROUTE_HASH,
            hash('sha256', $json),
            'Admin named-route inventory changed.',
        );

        foreach (
            [
                'filament.admin.pages.dashboard',
                'filament.admin.resources.account-heads.index',
                'filament.admin.resources.account-heads.create',
                'filament.admin.resources.account-heads.edit',
                'filament.admin.pages.account-ledger-report',
                'filament.admin.auth.login',
            ] as $routeName
        ) {
            $this->assertContains(
                $routeName,
                $adminNames,
            );
        }
    }

    public function test_admin_non_visual_dependencies_remain_byte_exact(): void
    {
        $hashes = [
            public_path('css/admin-theme.css') => self::ADMIN_LEGACY_THEME_HASH,
            public_path('css/karwan-premium-ui.css') => self::SHARED_UI_HASH,
            resource_path(
                'views/filament/admin/theme-head.blade.php',
            ) => self::ADMIN_THEME_HEAD_HASH,
            app_path(
                'Providers/Filament/AdminPanelProvider.php',
            ) => self::ADMIN_PANEL_PROVIDER_HASH,
            app_path(
                'Filament/Widgets/AiUsageOverview.php',
            ) => self::AI_USAGE_WIDGET_HASH,
            app_path(
                'Filament/Widgets/DashboardStats.php',
            ) => self::DASHBOARD_STATS_HASH,
            app_path(
                'Filament/Widgets/DocumentExpiryStats.php',
            ) => self::DOCUMENT_EXPIRY_STATS_HASH,
        ];

        foreach ($hashes as $path => $expectedHash) {
            $this->assertSame(
                $expectedHash,
                hash_file('sha256', $path),
                'Unexpected Admin dependency change: '.$path,
            );
        }
    }

    public function test_admin_foundation_remains_loaded_after_legacy_assets(): void
    {
        $head = file_get_contents(
            resource_path(
                'views/filament/admin/theme-head.blade.php',
            ),
        );

        $this->assertIsString($head);

        $sharedAsset =
            "asset('css/karwan-portal-premium-studio-v3.css')";

        $this->assertSame(
            1,
            substr_count($head, $sharedAsset),
        );
        $this->assertGreaterThan(
            strpos($head, 'karwan-premium-ui.css'),
            strpos($head, $sharedAsset),
        );
        $this->assertGreaterThan(
            strpos($head, 'admin-theme.css'),
            strpos($head, $sharedAsset),
        );
    }
}
