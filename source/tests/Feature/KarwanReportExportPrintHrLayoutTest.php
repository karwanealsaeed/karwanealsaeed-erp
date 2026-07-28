<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanReportExportPrintHrLayoutTest extends TestCase
{
    public function test_reports_export_route_supports_get_and_post_without_route_growth(): void
    {
        $route = Route::getRoutes()
            ->getByName('reports.export');

        $this->assertNotNull($route);
        $this->assertSame(
            'reports/{report}/{format}',
            $route->uri(),
        );
        $this->assertContains(
            'GET',
            $route->methods(),
        );
        $this->assertContains(
            'POST',
            $route->methods(),
        );
        $this->assertCount(
            430,
            Route::getRoutes(),
        );
    }

    public function test_visible_export_controller_is_authorized_sanitized_and_formula_safe(): void
    {
        $source = file_get_contents(
            app_path(
                'Http/Controllers/ReportExportController.php',
            ),
        );

        $this->assertIsString($source);

        foreach (
            [
                'PHASE 16F-3C2 VISIBLE REPORT EXPORT START',
                'exportVisibleReport(',
                'authorizeVisibleReportSource(',
                'sanitizeVisibleReportHtml(',
                'visibleExcelResponse(',
                'visibleExcelValue(',
                "'filament.admin.pages.'",
                "in_array(\$format, ['pdf', 'excel'], true)",
                "preg_match('/^[=+\\-@]/u'",
                'LIBXML_HTML_NOIMPLIED',
                "->setPaper('a4', 'landscape')",
                "'application/vnd.ms-excel; charset=UTF-8'",
                "'private, no-store, max-age=0'",
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $source,
            );
        }
        $visibleStart = strpos(
            $source,
            '    private function exportVisibleReport(',
        );
        $visibleEnd = strpos(
            $source,
            '    private function authorizeVisibleReportSource(',
            $visibleStart === false ? 0 : $visibleStart,
        );

        $this->assertNotFalse($visibleStart);
        $this->assertNotFalse($visibleEnd);
        $this->assertGreaterThan($visibleStart, $visibleEnd);

        $visibleMethod = substr(
            $source,
            $visibleStart,
            $visibleEnd - $visibleStart,
        );

        foreach (
            [
                'canViewReports()',
                'canDownloadReportPdf()',
                'canDownloadReportExcel()',
            ] as $permissionMarker
        ) {
            $this->assertStringContainsString(
                $permissionMarker,
                $visibleMethod,
            );
        }
    }

    public function test_admin_theme_loads_universal_report_export_runtime(): void
    {
        $head = file_get_contents(
            resource_path(
                'views/filament/admin/theme-head.blade.php',
            ),
        );
        $javascript = file_get_contents(
            public_path(
                'js/karwan-report-export-v3.js',
            ),
        );

        $this->assertIsString($head);
        $this->assertIsString($javascript);

        foreach (
            [
                'PHASE 16F-3C2 UNIVERSAL REPORT EXPORT START',
                'karwan-report-export-url',
                'karwan-current-admin-route',
                'karwan-report-csrf-token',
                'karwan-report-can-view',
                'karwan-report-can-pdf',
                'karwan-report-can-excel',
                'karwan-report-export-v3.js',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $head,
            );
        }

        foreach (
            [
                'PDF Download',
                'Excel Export',
                'data-karwan-export-actions',
                'exportPermission',
                'permittedFormats',
                '[data-karwan-report-primary-actions]',
                'window.print',
                'source_route',
                'source_url',
                'tables:',
                'text_rows:',
                'X-CSRF-TOKEN',
                'response.blob()',
                'livewire:navigated',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }
    }

    public function test_all_twenty_existing_print_pages_are_covered_without_blade_mutation(): void
    {
        $views = [
            0 => 'resources/views/filament/pages/account-ledger-report.blade.php',
            1 => 'resources/views/filament/pages/accounting-approval-center.blade.php',
            2 => 'resources/views/filament/pages/accounting-party-ledger-report.blade.php',
            3 => 'resources/views/filament/pages/accounting-posting-audit.blade.php',
            4 => 'resources/views/filament/pages/balance-sheet-report.blade.php',
            5 => 'resources/views/filament/pages/bank-reconciliation.blade.php',
            6 => 'resources/views/filament/pages/cash-bank-book-report.blade.php',
            7 => 'resources/views/filament/pages/customer-advance-balance-report.blade.php',
            8 => 'resources/views/filament/pages/customer-aging-report.blade.php',
            9 => 'resources/views/filament/pages/hr-reports.blade.php',
            10 => 'resources/views/filament/pages/passport-validity-report.blade.php',
            11 => 'resources/views/filament/pages/production-checklist.blade.php',
            12 => 'resources/views/filament/pages/profit-loss-report.blade.php',
            13 => 'resources/views/filament/pages/staff-payroll-report.blade.php',
            14 => 'resources/views/filament/pages/supplier-aging-report.blade.php',
            15 => 'resources/views/filament/pages/travel-insurance-report.blade.php',
            16 => 'resources/views/filament/pages/trial-balance-report.blade.php',
            17 => 'resources/views/filament/pages/umrah-package-poster.blade.php',
            18 => 'resources/views/filament/pages/unified-customer-statement.blade.php',
            19 => 'resources/views/filament/pages/unified-supplier-statement.blade.php',
        ];

        $this->assertCount(20, $views);

        foreach ($views as $relative) {
            $source = file_get_contents(
                base_path($relative),
            );

            $this->assertIsString($source);
            $this->assertTrue(
                str_contains(
                    $source,
                    'window.print(',
                )
                || str_contains(
                    $source,
                    '<x-report-actions',
                )
                || preg_match(
                    '/\bPrint\b/i',
                    $source,
                ) === 1,
                $relative,
            );
        }
    }

    public function test_hr_error_print_and_export_css_contracts_exist(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16F-3C2 UNIVERSAL REPORT EXPORT PRINT HR ERROR LAYOUT START',
                '.kes-report-export-actions',
                '.kes-report-export-action--pdf',
                '.kes-report-export-action--excel',
                '.hr-report-page',
                '.hr-filter-grid',
                '.hr-stat-grid',
                '.hr-stat-card',
                '.hr-stat-value',
                '.hr-table-wrap',
                '.hr-table',
                '[class*="max-h-"][class*="overflow-auto"][class*="whitespace-pre-wrap"]',
                '@media (max-width: 1024px)',
                '@media (max-width: 760px)',
                '@media print',
                '@page',
                'table-header-group',
                'page-break-inside: avoid',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }
    }

    public function test_pdf_export_template_is_print_safe(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/reports/visible-export-pdf.blade.php',
            ),
        );

        $this->assertIsString($view);

        foreach (
            [
                '@page',
                'A4',
                'landscape',
                'table-header-group',
                'page-break-inside: avoid',
                'DejaVu Sans',
                '{!! $content !!}',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $view,
            );
        }
    }
}
