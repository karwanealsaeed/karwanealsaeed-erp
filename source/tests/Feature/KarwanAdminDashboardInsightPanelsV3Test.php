<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAdminDashboardInsightPanelsV3Test extends TestCase
{
    private const ADMIN_ROUTE_HASH =
        'd1fe643943b269ecd2dba5cd2ce5a48c474bd9b20b4878575b724e632860f20d';

    private const DASHBOARD_BLADE_HASH =
        '0d71f06602443af3687bc99f973014bd9fb3c629a80281d0425ff8e7df8171b4';

    public function test_three_insight_panels_use_full_width_spacious_contract(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16F-2A5 ADMIN INSIGHT PANELS FULL-WIDTH FIX START',
                'PHASE 16F-2A5 ADMIN INSIGHT PANELS FULL-WIDTH FIX END',
                '.kes-revenue-card',
                '.kes-bookings-card',
                '.kes-payments-card',
                'grid-column: 1 / -1 !important;',
                'grid-template-columns:',
                'minmax(0, 1fr) !important;',
                'min-height:',
                'clamp(22rem, 31vw, 30rem)',
                'min-height: 16rem;',
                'min-width: 58rem;',
                '@media (max-width: 900px)',
                '@media (max-width: 640px)',
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
                'PHASE 16F-2A5 ADMIN INSIGHT PANELS FULL-WIDTH FIX START',
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $css,
                'PHASE 16F-2A5 ADMIN INSIGHT PANELS FULL-WIDTH FIX END',
            ),
        );
    }

    public function test_dashboard_blade_remains_byte_exact(): void
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
