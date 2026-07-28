<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanCustomerAgingLandscapePrintTest extends TestCase
{
    public function test_customer_aging_report_route_is_registered(): void
    {
        $route = Route::getRoutes()->getByName(
            'filament.admin.pages.customer-aging-report',
        );

        $this->assertNotNull($route);
        $this->assertSame(
            'admin/customer-aging-report',
            $route->uri(),
        );
        $this->assertContains('GET', $route->methods());
    }

    public function test_customer_aging_report_has_landscape_print_contract(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/filament/pages/customer-aging-report.blade.php',
            ),
        );

        $this->assertIsString($source);

        foreach ([
            'PHASE 16F-3D7 CUSTOMER AGING LANDSCAPE PRINT FIX START',
            'size: A4 landscape',
            'margin: 7mm',
            '.aging-stats',
            'repeat(4, minmax(0, 1fr))',
            '.aging-table-wrap',
            'overflow: visible !important',
            '.aging-table thead',
            'table-header-group',
            'page-break-inside: avoid',
            'white-space: normal !important',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }
    }

    public function test_existing_customer_aging_report_contracts_remain_present(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/filament/pages/customer-aging-report.blade.php',
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

        $this->assertSame(
            2,
            substr_count($source, '<table'),
        );
        $this->assertStringContainsString(
            'data-kes-final-four-print',
            $source,
        );
        $this->assertStringContainsString(
            'class="aging-page"',
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
