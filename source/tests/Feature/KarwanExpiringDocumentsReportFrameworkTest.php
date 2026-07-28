<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanExpiringDocumentsReportFrameworkTest extends TestCase
{
    public function test_expiring_documents_report_route_is_registered(): void
    {
        $route = Route::getRoutes()->getByName(
            'filament.admin.pages.expiring-documents-report',
        );

        $this->assertNotNull($route);
        $this->assertSame(
            'admin/expiring-documents-report',
            $route->uri(),
        );
        $this->assertContains('GET', $route->methods());
    }

    public function test_expiring_documents_report_uses_shared_toolbar_once(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/filament/pages/expiring-documents-report.blade.php',
            ),
        );

        $this->assertIsString($source);
        $this->assertSame(
            1,
            substr_count($source, '<x-report-actions />'),
        );
        $this->assertStringContainsString('<table', $source);
        $this->assertStringNotContainsString(
            'window.print(',
            $source,
        );
    }

    public function test_shared_framework_assets_cover_the_report(): void
    {
        $component = file_get_contents(
            resource_path('views/components/report-actions.blade.php'),
        );
        $javascript = file_get_contents(
            public_path('js/karwan-report-export-v3.js'),
        );
        $css = file_get_contents(
            public_path('css/karwan-portal-premium-studio-v3.css'),
        );

        $this->assertIsString($component);
        $this->assertIsString($javascript);
        $this->assertIsString($css);

        $this->assertStringContainsString(
            'data-karwan-report-primary-actions',
            $component,
        );
        $this->assertStringContainsString(
            '[data-karwan-export-actions]',
            $javascript,
        );
        $this->assertStringContainsString(
            'PHASE 16F-3D2 UNIVERSAL REPORT PRINT FRAMEWORK V2 START',
            $css,
        );
        $this->assertStringContainsString(
            '.overflow-x-auto',
            $css,
        );
        $this->assertStringContainsString(
            'overflow: visible !important',
            $css,
        );
    }
}
