<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class KarwanHrDedicatedPrintPreviewTest extends TestCase
{
    public function test_hr_button_uses_declarative_global_trigger(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/filament/pages/hr-reports.blade.php',
            ),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'data-kes-hr-print-preview',
            $source,
        );
        $this->assertStringNotContainsString(
            'onclick="window.kesHrPrintPreview()"',
            $source,
        );
        $this->assertStringNotContainsString(
            'PHASE 16F-3D8B HR DEDICATED PRINT PREVIEW START',
            $source,
        );
    }

    public function test_global_runtime_contains_iframe_print_bridge(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );

        $this->assertIsString($javascript);

        foreach ([
            'PHASE 16F-3D8C HR GLOBAL PRINT IFRAME BRIDGE START',
            '[data-kes-hr-print-preview]',
            'kes-hr-print-frame',
            'karwan-hr-iframe-print-style',
            'frame.contentDocument',
            'printDocument.importNode',
            'window.kesHrPrintPreview = openHrPrintPreview',
            'printWindow.print();',
            'event.stopPropagation();',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }
    }

    public function test_iframe_bridge_has_landscape_grid_contract(): void
    {
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );
        $css = file_get_contents(
            public_path(
                'css/karwan-portal-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($javascript);
        $this->assertIsString($css);

        foreach ([
            'size: A4 landscape',
            '.hr-report-page .hr-stat-grid',
            'repeat(3, minmax(0, 1fr))',
            '.hr-report-page .hr-table thead',
            'table-header-group',
        ] as $marker) {
            $this->assertStringContainsString(
                $marker,
                $javascript,
            );
        }

        $this->assertStringContainsString(
            'PHASE 16F-3D8B HR PRINT SPECIFICITY HARDENING START',
            $css,
        );
    }
}
