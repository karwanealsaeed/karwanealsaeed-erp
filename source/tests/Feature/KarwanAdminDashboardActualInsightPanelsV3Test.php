<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAdminDashboardActualInsightPanelsV3Test extends TestCase
{
    private const ADMIN_ROUTE_HASH =
        'd1fe643943b269ecd2dba5cd2ce5a48c474bd9b20b4878575b724e632860f20d';

    private const DASHBOARD_BLADE_HASH =
        '0d71f06602443af3687bc99f973014bd9fb3c629a80281d0425ff8e7df8171b4';

    public function test_real_dashboard_panels_have_semantic_full_width_classes(): void
    {
        $blade = file_get_contents(
            resource_path(
                'views/filament/pages/dashboard.blade.php',
            ),
        );

        $this->assertIsString($blade);
        $this->assertSame(
            1,
            substr_count(
                $blade,
                'class="kes-dashboard-panels"',
            ),
        );

        foreach (
            [
                'kes-insight-panel--revenue',
                'kes-insight-panel--bookings',
                'kes-insight-panel--payments',
            ] as $class
        ) {
            $this->assertSame(
                1,
                substr_count($blade, $class),
                'Missing or duplicated class: '.$class,
            );
        }

        $this->assertMatchesRegularExpression(
            '/kes-insight-panel--revenue[\s\S]*?Revenue Overview/',
            $blade,
        );
        $this->assertMatchesRegularExpression(
            '/kes-insight-panel--bookings[\s\S]*?Recent Bookings/',
            $blade,
        );
        $this->assertMatchesRegularExpression(
            '/kes-insight-panel--payments[\s\S]*?Recent Payments/',
            $blade,
        );
    }

    public function test_real_container_and_semantic_panels_use_one_column_contract(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16F-2A6 ACTUAL ACTUAL ADMIN INSIGHT PANELS FULL-WIDTH FIX START',
                'PHASE 16F-2A6 ACTUAL ACTUAL ADMIN INSIGHT PANELS FULL-WIDTH FIX END',
                '.kes-dashboard-panels',
                '.kes-insight-panel',
                '.kes-insight-panel--revenue',
                '.kes-insight-panel--bookings',
                '.kes-insight-panel--payments',
                'grid-template-columns:',
                'minmax(0, 1fr) !important;',
                'grid-column: 1 / -1 !important;',
                'clamp(24rem, 33vw, 32rem)',
                'min-width: 62rem;',
                '@media (max-width: 900px)',
                '@media (max-width: 640px)',
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
                'PHASE 16F-2A6 ACTUAL ACTUAL ADMIN INSIGHT PANELS FULL-WIDTH FIX START',
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $css,
                'PHASE 16F-2A6 ACTUAL ACTUAL ADMIN INSIGHT PANELS FULL-WIDTH FIX END',
            ),
        );
    }

    public function test_dashboard_blade_hash_matches_controlled_semantic_update(): void
    {
        $this->assertSame(
            self::DASHBOARD_BLADE_HASH,
            hash_file(
                'sha256',
                resource_path(
                    'views/filament/pages/dashboard.blade.php',
                ),
            ),
        );
    }

    public function test_route_contracts_remain_exact(): void
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
        $agentNames =
            array_values(array_unique($agentNames));
        sort($adminNames);

        $json = json_encode(
            $adminNames,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE,
        );

        $this->assertIsString($json);
        $this->assertCount(430, $routes);
        $this->assertCount(313, $adminNames);
        $this->assertCount(36, $agentNames);
        $this->assertSame(
            self::ADMIN_ROUTE_HASH,
            hash('sha256', $json),
        );
    }
}
