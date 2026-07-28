<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class KarwanPremiumStudioResponsiveRtlFormTest extends TestCase
{
    public function test_all_fourteen_inner_views_keep_exact_content_and_v3_layout(): void
    {
        $expected = [
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

        foreach ($expected as $relative => $hash) {
            $contents = file_get_contents(
                base_path($relative),
            );

            $this->assertIsString($contents);
            $this->assertSame(
                $hash,
                hash('sha256', $contents),
                'Public inner view changed unexpectedly: '.$relative,
            );
            $this->assertStringContainsString(
                "@extends('website.inner-v3-layout')",
                $contents,
            );
            $this->assertStringNotContainsString(
                "@extends('website.layout')",
                $contents,
            );
        }
    }

    public function test_existing_get_filter_forms_are_preserved(): void
    {
        foreach (
            [
                'blog',
                'faq',
                'gallery',
                'pakistan-tours',
                'promotions',
                'testimonials',
                'umrah-packages',
            ] as $view
        ) {
            $contents = file_get_contents(
                resource_path(
                    'views/website/'.$view.'.blade.php',
                ),
            );

            $this->assertIsString($contents);

            preg_match_all(
                '/<form\b[^>]*>/i',
                $contents,
                $matches,
            );

            $formTags = $matches[0] ?? [];

            $this->assertNotEmpty(
                $formTags,
                'Expected filter/search form missing: '.$view,
            );

            foreach ($formTags as $formTag) {
                $hasMethod = preg_match(
                    '/\bmethod\s*=/i',
                    $formTag,
                ) === 1;

                $isGet = preg_match(
                    '/\bmethod\s*=\s*["\']get["\']/i',
                    $formTag,
                ) === 1;

                $this->assertTrue(
                    ! $hasMethod || $isGet,
                    'Non-GET public filter form detected: '.$view,
                );
            }
        }
    }

    public function test_inner_css_has_scoped_responsive_rtl_and_form_foundation(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-premium-studio-v3-inner.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'KES V3 INNER RESPONSIVE RTL FORM FOUNDATION START',
                'KES V3 INNER RESPONSIVE RTL FORM FOUNDATION END',
                '[data-kes-v3-page="inner"] .kes-v3-inner-content',
                'html[dir="rtl"]',
                'min-block-size: 44px',
                'overflow-x: auto',
                '@media (max-width: 991px)',
                '@media (max-width: 767px)',
                '@media (prefers-reduced-motion: reduce)',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }

        $this->assertSame(
            1,
            substr_count(
                $css,
                'KES V3 INNER RESPONSIVE RTL FORM FOUNDATION START',
            ),
        );

        $this->assertSame(
            1,
            substr_count(
                $css,
                'KES V3 INNER RESPONSIVE RTL FORM FOUNDATION END',
            ),
        );
    }

    public function test_inner_layout_keeps_dynamic_direction_locale_and_complete_shell(): void
    {
        $layout = file_get_contents(
            resource_path(
                'views/website/inner-v3-layout.blade.php',
            ),
        );

        $this->assertIsString($layout);

        foreach (
            [
                "dir=\"{{ app()->isLocale('ur') ? 'rtl' : 'ltr' }}\"",
                'kes-locale-{{ app()->getLocale() }}',
                'data-kes-v3-page="inner"',
                "route('website.language'",
                "route('customer.portal.login')",
                "route('website.insurance')",
                "route('website.tours')",
                "route('website.legal.privacy'",
                "@include('components.karwan-ai-widget')",
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $layout,
            );
        }

        $this->assertStringNotContainsString(
            'Customer Portal',
            $layout,
        );
    }
}
