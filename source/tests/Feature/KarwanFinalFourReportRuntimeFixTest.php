<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanFinalFourReportRuntimeFixTest extends TestCase
{
    public function test_runtime_covers_all_four_remaining_paths(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($javascript);

        foreach ([
            'PHASE 16F-3D13 FINAL FOUR REPORT RUNTIME START',
            '/admin/customer-advance-balance-report',
            '/admin/customer-aging-report',
            '/admin/error-log-viewer',
            '/admin/expiring-documents-report',
            'filament.admin.pages.customer-advance-balance-report',
            'filament.admin.pages.customer-aging-report',
            'filament.admin.pages.error-log-viewer',
            'filament.admin.pages.expiring-documents-report',
            'kes-final-four-report-print-frame',
            'window.kesFinalFourReportPrint',
            'window.kesFinalFourVisibleExport',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }
    }

    public function test_runtime_has_landscape_print_and_visible_export_contracts(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($javascript);

        foreach ([
            'size: A4 landscape',
            'table-header-group',
            'page-break-inside: avoid',
            'white-space: normal !important',
            'printWindow.print();',
            'event.stopImmediatePropagation();',
            'source_route: config.route',
            'source_url: window.location.href',
            'response.blob()',
            "'X-CSRF-TOKEN': token",
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }
    }

    public function test_customer_reports_use_declarative_print_triggers(): void
    {
        foreach ([
            'customer-advance-balance-report.blade.php',
            'customer-aging-report.blade.php',
        ] as $file) {
            $source = file_get_contents(
                resource_path(
                    'views/filament/pages/'.$file,
                ),
            );

            $this->assertIsString($source);
            $this->assertStringContainsString(
                'data-kes-final-four-print',
                $source,
            );
            $this->assertStringNotContainsString(
                'onclick="window.print()"',
                $source,
            );
        }
    }

    public function test_error_and_expiring_pages_have_stable_responsive_roots(): void
    {
        $error = file_get_contents(
            resource_path(
                'views/filament/pages/error-log-viewer.blade.php',
            ),
        );
        $expiring = file_get_contents(
            resource_path(
                'views/filament/pages/expiring-documents-report.blade.php',
            ),
        );

        $this->assertIsString($error);
        $this->assertIsString($expiring);

        foreach ([
            '<x-report-actions />',
            'kes-error-log-report',
            'PHASE 16F-3D13 ERROR LOG RESPONSIVE CONTAINMENT START',
            'overflow-wrap: anywhere !important',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $error,
            );
        }

        foreach ([
            '<x-report-actions />',
            'kes-expiring-documents-report',
            'PHASE 16F-3D13 EXPIRING DOCUMENTS RESPONSIVE CONTAINMENT START',
            'min-width: 760px !important',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $expiring,
            );
        }
    }

    public function test_required_routes_and_visible_post_export_remain_registered(): void
    {
        $expected = [
            'filament.admin.pages.customer-advance-balance-report' => 'admin/customer-advance-balance-report',
            'filament.admin.pages.customer-aging-report' => 'admin/customer-aging-report',
            'filament.admin.pages.error-log-viewer' => 'admin/error-log-viewer',
            'filament.admin.pages.expiring-documents-report' => 'admin/expiring-documents-report',
        ];

        foreach ($expected as $name => $uri) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route);
            $this->assertSame($uri, $route->uri());
        }

        $export = Route::getRoutes()->getByName(
            'reports.export',
        );

        $this->assertNotNull($export);
        $this->assertSame(
            'reports/{report}/{format}',
            $export->uri(),
        );
        $this->assertContains(
            'POST',
            $export->methods(),
        );
    }
}
