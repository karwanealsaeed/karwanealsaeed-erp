<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanHrReportLandscapeCompactPrintTest extends TestCase
{
    public function test_hr_report_route_is_registered(): void
    {
        $route = Route::getRoutes()->getByName(
            'filament.admin.pages.hr-reports',
        );

        $this->assertNotNull($route);
        $this->assertSame(
            'admin/hr-reports',
            $route->uri(),
        );
        $this->assertContains('GET', $route->methods());
    }

    public function test_hr_report_has_landscape_compact_print_contract(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/filament/pages/hr-reports.blade.php',
            ),
        );

        $this->assertIsString($source);

        foreach ([
            'PHASE 16F-3D8 HR LANDSCAPE COMPACT PRINT FIX START',
            'size: A4 landscape',
            'margin: 6mm',
            '.hr-report-panel',
            'break-inside: auto !important',
            'repeat(3, minmax(0, 1fr))',
            ':has(> .hr-stat-card)',
            '.hr-table-wrap',
            '.hr-table thead',
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

    public function test_existing_hr_report_and_export_contracts_remain_present(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/filament/pages/hr-reports.blade.php',
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
            'data-kes-hr-print-preview',
            $source,
        );
        $this->assertStringContainsString(
            'hr-report-page',
            $source,
        );
        $this->assertStringContainsString(
            'hr-table',
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
