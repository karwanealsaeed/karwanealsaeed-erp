<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAdminResourceTablesV3Test extends TestCase
{
    private const ADMIN_ROUTE_HASH =
        'd1fe643943b269ecd2dba5cd2ce5a48c474bd9b20b4878575b724e632860f20d';

    public function test_resource_table_v3_css_covers_real_filament_surfaces(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16F-2B2 ADMIN RESOURCE TABLES FILTERS PAGINATION V3 START',
                'PHASE 16F-2B2 ADMIN RESOURCE TABLES FILTERS PAGINATION V3 END',
                '.fi-resource-list-records-page',
                '.fi-ta-ctn',
                '.fi-ta-header-toolbar',
                '.fi-ta-search-field',
                '.fi-ta-filters',
                '.fi-ta-filter-indicators',
                '.fi-ta-content',
                '.fi-ta-table',
                '.fi-ta-header-cell',
                '.fi-ta-actions-header-cell',
                '.fi-ta-row',
                '.fi-ta-cell',
                '.fi-ta-text',
                '.fi-ta-icon',
                '.fi-ta-image',
                '.fi-ta-color',
                '.fi-ta-record-checkbox',
                '.fi-ta-actions',
                '.fi-ta-bulk-actions',
                '.fi-ta-selection-indicator',
                '.fi-ta-reorder-handle',
                '.fi-ta-summary',
                '.fi-ta-empty-state',
                '.fi-pagination',
                '.fi-pagination-item',
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
                'PHASE 16F-2B2 ADMIN RESOURCE TABLES FILTERS PAGINATION V3 START',
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $css,
                'PHASE 16F-2B2 ADMIN RESOURCE TABLES FILTERS PAGINATION V3 END',
            ),
        );
    }

    public function test_resource_tables_use_public_v3_tokens_and_responsive_contracts(): void
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
                'var(--gold)',
                'var(--green)',
                'var(--green-2)',
                'var(--ink)',
                'var(--muted)',
                'var(--line)',
                'var(--shadow-md)',
                'radial-gradient(',
                'linear-gradient(',
                '@media (max-width: 1024px)',
                '@media (max-width: 760px)',
                'html[dir="rtl"]',
                '@media (prefers-reduced-motion: reduce)',
                ':focus-visible',
                'overflow-x: auto;',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }
    }

    public function test_admin_resource_route_inventory_is_exact(): void
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
            'Admin named-route inventory changed.',
        );

        foreach (
            [
                'filament.admin.resources.account-heads.index',
                'filament.admin.resources.customers.index',
                'filament.admin.resources.flight-bookings.index',
                'filament.admin.resources.visa-applications.index',
            ] as $routeName
        ) {
            $this->assertContains(
                $routeName,
                $adminNames,
            );
        }
    }
}
