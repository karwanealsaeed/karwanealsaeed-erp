<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAdminCustomDashboardPublicV3Test extends TestCase
{
    private const ADMIN_ROUTE_HASH =
        'd1fe643943b269ecd2dba5cd2ce5a48c474bd9b20b4878575b724e632860f20d';

    private const NON_VISUAL_HASHES =
        [
            'app/Filament/CustomPages/Dashboard.php' => '75cd86c7d7dd9fa64c270c9008ba173cd38334d64c45f39f911937bd8357a3bc',
            'resources/views/components/admin-scheduler-health.blade.php' => 'cb74943915ff89d0539659b046bf40fd978251ff9d960523bee009e79ddcad51',
            'resources/views/components/admin-error-health.blade.php' => '94a7f8497d04d97c7cebb31e69f738230a6c0c55038dc9fe0ea6fb3fb6c8cf18',
            'resources/views/components/admin-task-dashboard.blade.php' => '31a7fa388f8c3cbb02b9aad2298681565dbbe1563ef71055d978e39292dd99ce',
            'resources/views/components/admin-passport-dashboard.blade.php' => '1d8f0ce84f86c6a2ee4a90fe58f321024423363d00c76f30b3e5cc3f96c6e9fa',
            'app/Providers/Filament/AdminPanelProvider.php' => '10a9ed77cf8a86ee1141ba518e58f0456caf0feeb9c8ab6400734ba328629471',
            'resources/views/filament/admin/theme-head.blade.php' => '87e7aa5d5a8598cc093a86799ee11909e971a5c5701208ee858b851c05588e98',
            'public/css/admin-theme.css' => '635cbca6b1ebe9daf418e81182ef388f57defd33f47d0e8ca32b49bb16b78e14',
            'public/css/karwan-premium-ui.css' => 'f1b713b78ce43cc276d6c025131eeef52354e75e6e385fb0722c1b14d8332c33',
        ];

    public function test_custom_dashboard_blade_uses_public_v3_contract(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/filament/pages/dashboard.blade.php',
            ),
        );

        $this->assertIsString($view);

        foreach (
            [
                'PHASE 16F-2A3 ADMIN CUSTOM DASHBOARD PUBLIC V3 CORRECTION START',
                'PHASE 16F-2A3 ADMIN CUSTOM DASHBOARD PUBLIC V3 CORRECTION END',
                'kes-admin-dashboard kes-admin-dashboard-v3',
                'data-kes-admin-dashboard="premium-studio-v3"',
                'kes-admin-dashboard-eyebrow',
                'Premium Studio V3 · Admin Control Centre',
                'kes-admin-dashboard-health',
                'data-kes-admin-dashboard-health="technical-diagnostics"',
                'System Health & Unresolved Incidents',
                'Technical details collapsed by default',
                '<x-admin-scheduler-health />',
                '<x-admin-error-health />',
                '<x-admin-task-dashboard />',
                '<x-admin-passport-dashboard />',
                'class="kes-dashboard-footer"',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $view,
            );
        }

        $this->assertSame(
            1,
            substr_count(
                $view,
                '<x-admin-scheduler-health />',
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $view,
                '<x-admin-error-health />',
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $view,
                'data-kes-admin-dashboard="premium-studio-v3"',
            ),
        );

        $titlePosition =
            strpos($view, 'class="kes-dashboard-title-row"');
        $healthPosition =
            strpos($view, 'class="kes-admin-dashboard-health"');
        $footerPosition =
            strpos($view, 'class="kes-dashboard-footer"');

        $this->assertIsInt($titlePosition);
        $this->assertIsInt($healthPosition);
        $this->assertIsInt($footerPosition);
        $this->assertGreaterThan(
            $titlePosition,
            $healthPosition,
            'Technical incidents must not dominate the page top.',
        );
        $this->assertLessThan(
            $footerPosition,
            $healthPosition,
            'Technical diagnostics must remain before the dashboard footer.',
        );
    }

    public function test_custom_dashboard_css_covers_actual_kes_surfaces(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16F-2A3 ADMIN CUSTOM DASHBOARD PUBLIC V3 CORRECTION START',
                'PHASE 16F-2A3 ADMIN CUSTOM DASHBOARD PUBLIC V3 CORRECTION END',
                '.kes-admin-dashboard-v3',
                '.kes-dashboard-title-row',
                '.kes-admin-dashboard-eyebrow',
                '.kes-welcome-card',
                '.kes-stats-grid',
                '.kes-stat-card',
                '.kes-stat-icon',
                '.kes-stat-value',
                '.kes-dashboard-grid',
                '.kes-passport-head',
                '.kes-dashboard-footer',
                '.kes-admin-dashboard-health',
                '.kes-admin-dashboard-health__content',
                '.fi-sidebar-group.fi-collapsed',
                '@media (max-width: 1180px)',
                '@media (max-width: 900px)',
                '@media (max-width: 640px)',
                'html[dir="rtl"]',
                '@media (prefers-reduced-motion: reduce)',
                'var(--display)',
                'var(--gold)',
                'var(--green)',
                'var(--surface)',
                'radial-gradient(',
                'linear-gradient(',
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
                'PHASE 16F-2A3 ADMIN CUSTOM DASHBOARD PUBLIC V3 CORRECTION START',
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $css,
                'PHASE 16F-2A3 ADMIN CUSTOM DASHBOARD PUBLIC V3 CORRECTION END',
            ),
        );
    }

    public function test_admin_route_contract_remains_exact(): void
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
        $this->assertCount(
            36,
            array_unique($agentNames),
        );
        $this->assertSame(
            self::ADMIN_ROUTE_HASH,
            hash('sha256', $json),
        );
    }

    public function test_non_visual_dashboard_dependencies_remain_byte_exact(): void
    {
        foreach (
            self::NON_VISUAL_HASHES as $relativePath => $expectedHash
        ) {
            $path = base_path($relativePath);

            $this->assertFileExists($path);
            $this->assertSame(
                $expectedHash,
                hash_file('sha256', $path),
                'Unexpected non-visual change: '.$relativePath,
            );
        }
    }
}
