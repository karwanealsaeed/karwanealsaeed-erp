<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanThreeConfirmedReportPrintFailuresTest extends TestCase
{
    public function test_special_report_runtime_covers_all_three_paths(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($javascript);

        foreach ([
            'PHASE 16F-3D9 THREE CONFIRMED REPORT PRINT FIXES START',
            '/admin/passport-validity-report',
            '/admin/supplier-aging-report',
            '/admin/travel-insurance-report',
            'passport-validity',
            'supplier-aging',
            'travel-insurance',
            'kes-confirmed-report-print-frame',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }
    }

    public function test_special_runtime_has_landscape_and_compact_layout(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($javascript);

        foreach ([
            'size: A4 landscape',
            'repeat(3, minmax(0, 1fr))',
            'data-kes-print-summary-grid',
            'data-kes-print-summary-card',
            'table-header-group',
            'page-break-inside: avoid',
            'white-space: normal !important',
            'printWindow.print();',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }
    }

    public function test_print_click_is_intercepted_before_legacy_handlers(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($javascript);
        $this->assertStringContainsString(
            'event.stopImmediatePropagation();',
            $javascript,
        );
        $this->assertStringContainsString(
            "onclick.includes(\"kesreportdownload('print')\")",
            $javascript,
        );
        $this->assertStringContainsString(
            'window.kesConfirmedReportPrint',
            $javascript,
        );
    }

    public function test_dedicated_travel_insurance_route_remains_registered(): void
    {
        $route = Route::getRoutes()->getByName(
            'travel-insurance-report.export',
        );

        $this->assertNotNull($route);
        $this->assertSame(
            'travel-insurance-report/{format}',
            $route->uri(),
        );
        $this->assertContains('GET', $route->methods());
    }
}
