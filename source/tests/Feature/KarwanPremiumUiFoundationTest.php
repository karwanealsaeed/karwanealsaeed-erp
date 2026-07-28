<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class KarwanPremiumUiFoundationTest extends TestCase
{
    public function test_all_primary_layouts_load_premium_ui_assets(): void
    {
        $layouts = [
            resource_path('views/website/layout.blade.php'),
            resource_path('views/customer-portal/layout.blade.php'),
            resource_path('views/filament/admin/theme-head.blade.php'),
        ];

        foreach ($layouts as $layout) {
            $source = file_get_contents($layout);

            $this->assertIsString($source);
            $this->assertStringContainsString(
                'KES PREMIUM UI FOUNDATION START',
                $source,
            );
            $this->assertStringContainsString(
                "asset('css/karwan-premium-ui.css')",
                $source,
            );
            $this->assertStringContainsString(
                "asset('js/karwan-premium-ui.js')",
                $source,
            );
            $this->assertStringContainsString(
                'family=Cormorant+Garamond',
                $source,
            );
            $this->assertStringContainsString(
                'family=Manrope',
                $source,
            );
            $this->assertStringContainsString(
                'family=Noto+Naskh+Arabic',
                $source,
            );
        }
    }

    public function test_foundation_css_uses_exact_brand_palette_and_accessibility(): void
    {
        $source = file_get_contents(
            public_path('css/karwan-premium-ui.css'),
        );

        $this->assertIsString($source);

        foreach (
            [
                '#fef8d4',
                '#b35c22',
                '#f9cb20',
                '#211d1c',
                '#ea212b',
            ] as $colour
        ) {
            $this->assertStringContainsString($colour, $source);
        }

        $this->assertStringContainsString(
            '@media (prefers-reduced-motion: reduce)',
            $source,
        );
        $this->assertStringContainsString(
            '.kes-cursor',
            $source,
        );
        $this->assertStringContainsString(
            '.kes-reveal',
            $source,
        );
        $this->assertStringContainsString(
            'Noto Naskh Arabic',
            $source,
        );
    }

    public function test_foundation_javascript_is_progressive_and_touch_safe(): void
    {
        $source = file_get_contents(
            public_path('js/karwan-premium-ui.js'),
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'IntersectionObserver',
            $source,
        );
        $this->assertStringContainsString(
            'requestAnimationFrame',
            $source,
        );
        $this->assertStringContainsString(
            'prefers-reduced-motion: reduce',
            $source,
        );
        $this->assertStringContainsString(
            '(hover: hover) and (pointer: fine)',
            $source,
        );
        $this->assertStringContainsString(
            'livewire:navigated',
            $source,
        );
        $this->assertStringNotContainsString(
            'innerHTML',
            $source,
        );
    }
}
