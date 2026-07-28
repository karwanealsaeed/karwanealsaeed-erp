<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanAdminCustomPagesWidgetsV3Test extends TestCase
{
    private const ADMIN_ROUTE_HASH =
        'd1fe643943b269ecd2dba5cd2ce5a48c474bd9b20b4878575b724e632860f20d';

    public function test_custom_pages_reports_and_widgets_v3_css_contract_exists(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16F-3B1 ADMIN CUSTOM PAGES REPORTS WIDGETS V3 START',
                'PHASE 16F-3B1 ADMIN CUSTOM PAGES REPORTS WIDGETS V3 END',
                '.fi-wi-stats-overview-stat',
                '.fi-wi-chart',
                '.fi-wi-table',
                '.fi-in-entry-wrp',
                '.fi-in-text',
                '.fi-in-icon',
                '.fi-tabs',
                '.fi-tabs-item',
                'canvas',
                'table:not(.fi-ta-table)',
                '[class*="report-filter"]',
                '[class*="report-card"]',
                '[class*="statement-card"]',
                '[class*="health-card"]',
                '[class*="error-card"]',
                '[class*="backup-card"]',
                '[class*="ai-card"]',
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
                'PHASE 16F-3B1 ADMIN CUSTOM PAGES REPORTS WIDGETS V3 START',
            ),
        );
        $this->assertSame(
            1,
            substr_count(
                $css,
                'PHASE 16F-3B1 ADMIN CUSTOM PAGES REPORTS WIDGETS V3 END',
            ),
        );
    }

    public function test_custom_pages_v3_uses_public_tokens_responsive_rtl_and_print_contracts(): void
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
                'radial-gradient(',
                'linear-gradient(',
                '@media (max-width: 1024px)',
                '@media (max-width: 760px)',
                'html[dir="rtl"]',
                '@media (prefers-reduced-motion: reduce)',
                '@media print',
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

    public function test_admin_custom_page_route_inventory_is_exact(): void
    {
        $routes = Route::getRoutes();
        $adminNames = [];
        $agentNames = [];
        $customPageNames = [];

        foreach ($routes as $route) {
            $name = (string) ($route->getName() ?? '');

            if (str_starts_with($name, 'filament.admin.')) {
                $adminNames[] = $name;
            }

            if (str_starts_with($name, 'customer.portal.')) {
                $agentNames[] = $name;
            }

            if (str_starts_with($name, 'filament.admin.pages.')) {
                $customPageNames[] = $name;
            }
        }

        $adminNames =
            array_values(array_unique($adminNames));
        $agentNames =
            array_values(array_unique($agentNames));
        $customPageNames =
            array_values(array_unique($customPageNames));
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
        $this->assertCount(46, $customPageNames);
        $this->assertSame(
            self::ADMIN_ROUTE_HASH,
            hash('sha256', $json),
        );

        foreach (
            [
                'filament.admin.pages.account-ledger-report',
                'filament.admin.pages.accounting-approval-center',
                'filament.admin.pages.system-health-check',
                'filament.admin.pages.error-log-viewer',
                'filament.admin.pages.email-smtp-test',
                'filament.admin.pages.hr-reports',
                'filament.admin.pages.dashboard',
            ] as $routeName
        ) {
            $this->assertContains(
                $routeName,
                $adminNames,
            );
        }
    }
}
