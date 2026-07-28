<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanPremiumStudioInnerLayoutTest extends TestCase
{
    public function test_inner_layout_inherits_complete_approved_v3_shell(): void
    {
        $homeLayout = file_get_contents(
            resource_path(
                'views/website/home-v3-layout.blade.php',
            ),
        );

        $innerLayout = file_get_contents(
            resource_path(
                'views/website/inner-v3-layout.blade.php',
            ),
        );

        $this->assertIsString($homeLayout);
        $this->assertIsString($innerLayout);

        foreach (
            [
                "route('website.home')",
                'website.about',
                'website.services',
                'website.umrah',
                'website.visas',
                'website.tickets',
                'website.hotels',
                'website.insurance',
                'website.tours',
                'website.promotions',
                'website.gallery',
                'website.blog',
                'website.faq',
                'website.contact',
                'website.legal.privacy',
                'website.legal.refund',
                'website.legal.cancellation',
                'website.legal.disclaimer',
                'website.legal.terms',
                'website.language',
                'customer.portal.login',
                'canonical',
                'hreflang',
                'property="og:',
                'name="twitter:',
                'application/ld+json',
                'manifest.webmanifest',
                'serviceWorker',
                "@include('components.karwan-ai-widget')",
                'karwan-premium-studio-v3.css',
                'karwan-premium-studio-v3.js',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $innerLayout,
            );
        }

        $this->assertStringContainsString(
            '$company = $company ?? null;',
            $innerLayout,
        );

        $this->assertStringContainsString(
            'data-kes-v3-page="inner"',
            $innerLayout,
        );

        $this->assertStringContainsString(
            'class="kes-v3-inner-content"',
            $innerLayout,
        );

        $this->assertStringContainsString(
            "asset('css/karwan-premium-studio-v3-inner.css')",
            $innerLayout,
        );

        $this->assertStringNotContainsString(
            'Customer Portal',
            $innerLayout,
        );
    }

    public function test_inner_layout_preserves_home_shell_and_restores_homepage_only_options(): void
    {
        $homeLayout = file_get_contents(
            resource_path(
                'views/website/home-v3-layout.blade.php',
            ),
        );

        $innerLayout = file_get_contents(
            resource_path(
                'views/website/inner-v3-layout.blade.php',
            ),
        );

        $this->assertIsString($homeLayout);
        $this->assertIsString($innerLayout);

        preg_match_all(
            '/route\s*\(\s*[\'"]([^\'"]+)[\'"]/',
            $homeLayout,
            $homeMatches,
        );

        preg_match_all(
            '/route\s*\(\s*[\'"]([^\'"]+)[\'"]/',
            $innerLayout,
            $innerMatches,
        );

        $homeRoutes = array_values(
            array_unique($homeMatches[1] ?? []),
        );

        $expectedInnerRoutes = array_values(
            array_unique(
                [
                    ...$homeRoutes,
                    'website.insurance',
                    'website.tours',
                ],
            ),
        );

        $innerRoutes = array_values(
            array_unique($innerMatches[1] ?? []),
        );

        sort($expectedInnerRoutes);
        sort($innerRoutes);

        $this->assertSame(
            $expectedInnerRoutes,
            $innerRoutes,
        );

        foreach ($innerRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                'Inner shell references missing route: '.$routeName,
            );
        }
    }

    public function test_inner_layout_css_is_scoped_and_non_destructive(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-premium-studio-v3-inner.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                '[data-kes-v3-page="inner"] .kes-v3-inner-content',
                'min-height: 58vh',
                'scroll-margin-top: 7rem',
                'max-width: 100%',
                '@media (max-width: 767px)',
                '@media print',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }

        $this->assertStringNotContainsString(
            'body {',
            $css,
        );

        $this->assertStringNotContainsString(
            '* {',
            $css,
        );
    }

    public function test_controlled_inner_views_use_v3_layout_and_none_remain_legacy(): void
    {
        foreach (
            [
                'page',
                'legal-page',
                'faq',
                'testimonials',
                'blog-detail',
                'blog',
                'gallery-detail',
                'gallery',
                'promotion-detail',
                'promotions',
                'pakistan-tour-detail',
                'pakistan-tours',
                'umrah-package-detail',
                'umrah-packages',
            ] as $view
        ) {
            $contents = file_get_contents(
                resource_path(
                    'views/website/'.$view.'.blade.php',
                ),
            );

            $this->assertIsString($contents);
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
}
