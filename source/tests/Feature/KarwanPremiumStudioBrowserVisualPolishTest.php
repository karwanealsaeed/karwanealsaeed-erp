<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class KarwanPremiumStudioBrowserVisualPolishTest extends TestCase
{
    private const VIEW_HASHES = [
        'resources/views/website/page.blade.php' => 'ebce5b40607de71855f31ac4c701bde1fcd875c06f469473724afc6415a6aa45',
        'resources/views/website/legal-page.blade.php' => 'eb2474b5abce97483cc78d622cd75f0d3748c50281038a78c327abd012f2e394',
        'resources/views/website/faq.blade.php' => '8673fcba2ca42828dbd035c50f1dc6bb39a160a6869727a740908651dc122d90',
        'resources/views/website/testimonials.blade.php' => 'dc30cba1a3128918ef2c8e1272b11066674622f126a194cd0d9deb7f5ce8efe6',
        'resources/views/website/blog-detail.blade.php' => '905049af613448611d66e66d450a7406d39503018b3fe4cabe9fceb0f92f3973',
        'resources/views/website/blog.blade.php' => '3515e1f421b2bb6699914278d63f4e2972d98892362aeb2cd8d8418dd0454c2a',
        'resources/views/website/gallery-detail.blade.php' => 'b091f938715a8c71d2b290b8062e350b2914882350a9039e43aff32325e0f880',
        'resources/views/website/gallery.blade.php' => '10d7fe0f3b27a1e836367eb5deee2b4e3cfd1f7d3e6741e0463a651394723410',
        'resources/views/website/promotion-detail.blade.php' => '3b068bab28d8a7328f705eeb0503711def2363e08bc530df3077e1ac0191d8e8',
        'resources/views/website/promotions.blade.php' => '51d0e2777661b91961958b1de8144a5383473d9a3364203b47ba7253077ad32f',
        'resources/views/website/pakistan-tour-detail.blade.php' => 'faae817a4384e9db12db0d3c8a00fabd947f99f9617a354effbe9bbf76f93342',
        'resources/views/website/pakistan-tours.blade.php' => 'bfbc8491ecfc9defcd6d962b078c1deb0b33be515a4cdb4578cda4de9dcfe39e',
        'resources/views/website/umrah-package-detail.blade.php' => '3e324b26ef8ca703350db35805090a43b61712fb2f9100084cbe339ac5d05c22',
        'resources/views/website/umrah-packages.blade.php' => 'eb5cb93ed26f1b60c3045713428ad8e1655d0666f44bf08cd78b4adc67937e1e',
    ];

    public function test_testimonials_translation_key_renders_a_real_label(): void
    {
        foreach (
            [
                'resources/views/website/home-v3-layout.blade.php',
                'resources/views/website/inner-v3-layout.blade.php',
            ] as $relative
        ) {
            $contents = file_get_contents(
                base_path($relative),
            );

            $this->assertIsString($contents);
            $this->assertStringNotContainsString(
                'website.nav.testimonials',
                $contents,
            );
            $this->assertGreaterThanOrEqual(
                2,
                substr_count(
                    $contents,
                    "__('website.testimonials')",
                ),
            );
        }

        $this->assertSame(
            'Testimonials',
            __('website.testimonials', [], 'en'),
        );
        $this->assertSame(
            'کسٹمر آراء',
            __('website.testimonials', [], 'ur'),
        );
    }

    public function test_homepage_glass_layers_are_stable_for_full_page_capture(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'KES V3 VISUAL COMPOSITING AND SERVICE TILE FIX START',
                '.kes-v3-service-tabs',
                '.kes-premium-studio-home .service-tile',
                '-webkit-backdrop-filter: none !important',
                'backdrop-filter: none !important',
                'contain: paint',
                'transform-style: flat',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }
    }

    public function test_inner_hero_has_full_header_clearance_and_no_blur_compositing(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-premium-studio-v3-inner.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'KES V3 INNER HERO CLEARANCE AND COMPOSITING FIX START',
                '.kes-v3-inner-content',
                'padding-top: 58px',
                '[class*="hero-logo"]',
                'filter: none !important',
                'backdrop-filter: none !important',
                'isolation: isolate',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }
    }

    public function test_visible_navigation_and_complete_footer_are_preserved(): void
    {
        foreach (
            [
                'resources/views/website/home-v3-layout.blade.php',
                'resources/views/website/inner-v3-layout.blade.php',
            ] as $relative
        ) {
            $contents = file_get_contents(
                base_path($relative),
            );

            $this->assertIsString($contents);
            $this->assertStringContainsString(
                'KES V3 COMPLETE VISIBLE SERVICE TABS START',
                $contents,
            );
            $this->assertStringContainsString(
                'KES V3 COMPLETE FOOTER DIRECTORY START',
                $contents,
            );

            foreach (
                [
                    "route('website.umrah')",
                    "route('website.visas')",
                    "route('website.tickets')",
                    "route('website.hotels')",
                    "route('website.services')",
                    "route('website.insurance')",
                    "route('website.tours')",
                    "route('website.consultancy')",
                    "route('website.promotions')",
                ] as $routeMarker
            ) {
                $this->assertStringContainsString(
                    $routeMarker,
                    $contents,
                );
            }
        }
    }

    public function test_all_public_inner_view_bodies_remain_unchanged(): void
    {
        foreach (
            self::VIEW_HASHES as $relative => $expectedHash
        ) {
            $contents = file_get_contents(
                base_path($relative),
            );

            $this->assertIsString($contents);
            $this->assertSame(
                $expectedHash,
                hash('sha256', $contents),
                'Public inner view changed: '
                .$relative,
            );
        }
    }
}
