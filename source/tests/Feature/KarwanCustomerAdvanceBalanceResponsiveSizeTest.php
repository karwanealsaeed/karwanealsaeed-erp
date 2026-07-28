<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanCustomerAdvanceBalanceResponsiveSizeTest extends TestCase
{
    public function test_customer_advance_balance_report_route_is_registered(): void
    {
        $route = Route::getRoutes()->getByName(
            'filament.admin.pages.customer-advance-balance-report',
        );

        $this->assertNotNull($route);
        $this->assertSame(
            'admin/customer-advance-balance-report',
            $route->uri(),
        );
        $this->assertContains('GET', $route->methods());
    }

    public function test_customer_advance_report_contains_responsive_size_fix(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/filament/pages/customer-advance-balance-report.blade.php',
            ),
        );

        $this->assertIsString($source);

        foreach ([
            'PHASE 16F-3D6 V2 CUSTOMER ADVANCE RESPONSIVE SIZE FIX START',
            '.advance-report',
            '.advance-filters',
            'repeat(3, minmax(0, 1fr))',
            '.advance-buttons',
            'grid-column: 1 / -1',
            '.advance-table-wrap',
            'overflow-x: auto !important',
            'min-width: 1180px !important',
            '@media (max-width: 1280px)',
            '@media (max-width: 760px)',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }
    }

    public function test_existing_report_and_export_contracts_remain_present(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/filament/pages/customer-advance-balance-report.blade.php',
            ),
        );
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );
        $css = file_get_contents(
            public_path('css/karwan-portal-premium-studio-v3.css'),
        );

        $this->assertIsString($source);
        $this->assertIsString($javascript);
        $this->assertIsString($css);

        $this->assertStringContainsString(
            'data-kes-final-four-print',
            $source,
        );
        $this->assertStringContainsString(
            'class="advance-table"',
            $source,
        );
        $this->assertStringContainsString(
            '[data-karwan-export-actions]',
            $javascript,
        );
        $this->assertStringContainsString(
            'PHASE 16F-3D2 UNIVERSAL REPORT PRINT FRAMEWORK V2 START',
            $css,
        );
    }
}
