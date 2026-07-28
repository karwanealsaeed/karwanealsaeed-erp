<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class KarwanUniversalReportFrameworkV2Test extends TestCase
{
    public function test_shared_report_actions_has_one_primary_print_control(): void
    {
        $source = file_get_contents(
            resource_path('views/components/report-actions.blade.php'),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString('Print Preview', $source);
        $this->assertStringContainsString(
            'data-karwan-report-primary-actions',
            $source,
        );
        $this->assertSame(1, substr_count($source, '<button'));
        $this->assertStringNotContainsString('Download PDF', $source);
        $this->assertStringNotContainsString('Download Excel', $source);
    }

    public function test_representative_reports_use_one_shared_toolbar(): void
    {
        $files = [
            'filament/pages/daily-finance-report.blade.php',
            'filament/pages/monthly-finance-report.blade.php',
            'filament/pages/yearly-finance-report.blade.php',
            'filament/pages/date-range-finance-report.blade.php',
            'filament/pages/booking-status-report.blade.php',
            'filament/pages/airline-wise-flight-report.blade.php',
            'filament/pages/route-wise-flight-report.blade.php',
            'filament/pages/hotel-wise-report.blade.php',
            'filament/pages/umrah-package-wise-report.blade.php',
            'filament/pages/account-ledger-report.blade.php',
            'filament/pages/travel-insurance-report.blade.php',
            'filament/pages/error-log-viewer.blade.php',
        ];

        foreach ($files as $file) {
            $source = file_get_contents(resource_path('views/'.$file));

            $this->assertIsString($source, $file);
            $this->assertSame(
                1,
                substr_count($source, '<x-report-actions />'),
                $file,
            );
        }
    }

    public function test_hr_uses_standard_print_preview_label(): void
    {
        $source = file_get_contents(
            resource_path('views/filament/pages/hr-reports.blade.php'),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString('Print Preview', $source);
        $this->assertStringNotContainsString(
            '>Print Report<',
            preg_replace('/\s+/', ' ', $source) ?? $source,
        );
    }

    public function test_universal_js_contains_duplicate_control_guard(): void
    {
        $source = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($source);

        foreach ([
            'PHASE 16F-3D2 UNIVERSAL REPORT TOOLBAR DEDUPLICATION START',
            'deduplicateReportActions',
            '[data-karwan-export-actions]',
            'download excel csv',
            'MutationObserver',
        ] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }

    public function test_universal_print_css_removes_report_overflow(): void
    {
        $source = file_get_contents(
            public_path('css/karwan-portal-premium-studio-v3.css'),
        );

        $this->assertIsString($source);

        foreach ([
            'PHASE 16F-3D2 UNIVERSAL REPORT PRINT FRAMEWORK V2 START',
            'size: A4 landscape',
            '.hr-table-wrap',
            '.kes-report-table-wrap',
            'table-header-group',
            'overflow: visible !important',
            'white-space: normal !important',
            'page-break-inside: avoid',
        ] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }
}
