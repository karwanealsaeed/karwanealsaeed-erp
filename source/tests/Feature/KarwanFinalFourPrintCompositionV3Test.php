<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class KarwanFinalFourPrintCompositionV3Test extends TestCase
{
    public function test_v3_uses_named_landscape_page_and_full_size_frame(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($javascript);

        foreach ([
            'PHASE 16F-3D14 FINAL FOUR PRINT COMPOSITION V3 START',
            '@page kesFinalFourLandscape',
            'page: kesFinalFourLandscape',
            "width: '297mm'",
            "height: '210mm'",
            "left: '-12000px'",
            "'print'",
            'PHASE 16F-3D8C HR GLOBAL PRINT IFRAME BRIDGE START',
            'PHASE 16F-3D9 THREE CONFIRMED REPORT PRINT FIXES START',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }
    }

    public function test_v3_marks_customer_advance_and_aging_summary_grids(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($javascript);

        foreach ([
            'annotateKnownSummaryGrid',
            "root.querySelector('.advance-cards')",
            "root.querySelector('.aging-stats')",
            'repeat(4, minmax(0, 1fr))',
            'data-kes-final-summary-grid',
            'data-kes-final-summary-card',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }
    }

    public function test_v3_prints_error_incidents_without_raw_log_or_action_column(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($javascript);

        foreach ([
            'prepareErrorLogPrint',
            "root.querySelectorAll('pre')",
            "'tr > :last-child'",
            'table-layout: fixed !important',
            'width: 39% !important',
            'mode !== \'print\'',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }
    }
}
