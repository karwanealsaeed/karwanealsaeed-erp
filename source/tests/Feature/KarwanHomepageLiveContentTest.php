<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanHomepageLiveContentTest extends TestCase
{
    public function test_homepage_source_retires_portal_mock_and_registers_live_sections(): void
    {
        $home = file_get_contents(
            resource_path(
                'views/website/home.blade.php',
            ),
        );
        $partial = file_get_contents(
            resource_path(
                'views/website/partials/home-latest-content.blade.php',
            ),
        );

        $this->assertIsString($home);
        $this->assertIsString($partial);

        foreach (
            [
                'PHASE 16D-4F7 LIVE HOMEPAGE CONTENT START',
                "route('customer.portal.login')",
                "@include('website.partials.home-latest-content')",
            ] as $homeMarker
        ) {
            $this->assertStringContainsString(
                $homeMarker,
                $home,
            );
        }

        foreach (
            [
                'Customer Reviews',
                'Latest News',
                'Latest Promotions',
                'home-live-content',
                'home-live-panel',
            ] as $partialMarker
        ) {
            $this->assertStringContainsString(
                $partialMarker,
                $partial,
            );
        }

        foreach (
            [
                'One premium design system',
                'Website and Agent Portal—fully connected.',
                'The same logo-led visual language continues',
                'Open Agent Portal',
                'Explore services',
                'interface-preview',
                'interface-mock',
                'mini-logo',
            ] as $retiredMarker
        ) {
            $this->assertStringNotContainsString(
                $retiredMarker,
                $home,
            );
        }
    }

    public function test_homepage_source_uses_service_and_live_partial_contract(): void
    {
        $home = file_get_contents(
            resource_path(
                'views/website/home.blade.php',
            ),
        );
        $partial = file_get_contents(
            resource_path(
                'views/website/partials/home-latest-content.blade.php',
            ),
        );

        $this->assertIsString($home);
        $this->assertIsString($partial);

        $this->assertStringContainsString(
            'PHASE 16D-4F7 LIVE HOMEPAGE CONTENT START',
            $home,
        );
        $this->assertStringContainsString(
            "@include('website.partials.home-latest-content')",
            $home,
        );

        foreach (
            [
                'HomepageContentService',
                'homepageTestimonials',
                'homepageNews',
                'homepagePromotions',
                "route('website.testimonials')",
                "route('website.blog')",
                "route('website.promotions')",
                'Customer Reviews',
                'Latest News',
                'Latest Promotions',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $partial,
            );
        }
    }

    public function test_homepage_content_service_is_publication_aware(): void
    {
        $service = file_get_contents(
            app_path(
                'Services/HomepageContentService.php',
            ),
        );

        $this->assertIsString($service);
        foreach (
            [
                'is_published',
                'is_active',
                "'status'",
                'published_at',
                'starts_at',
                'ends_at',
                'limit(3)',
                'website.blog.show',
                'website.promotions.show',
                'strip_tags',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $service,
            );
        }
    }

    public function test_service_cards_receive_exact_public_v3_contrast_polish(): void
    {
        $css = file_get_contents(
            public_path(
                'css/karwan-premium-studio-v3.css',
            ),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16D-4F7 HOMEPAGE SERVICES AND LIVE CONTENT START',
                '#services .service-tile__icon',
                '#services .service-tile h3',
                '#services .service-tile p',
                '#services .service-tile--gold',
                '.home-live-content__grid',
                '.home-review-card',
                '.home-news-card',
                '.home-promotion-card',
                '@media (max-width: 1080px)',
                '@media (max-width: 720px)',
                'html[dir="rtl"]',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }
    }

    public function test_public_content_routes_remain_available(): void
    {
        foreach (
            [
                'website.home',
                'website.testimonials',
                'website.blog',
                'website.blog.show',
                'website.promotions',
                'website.promotions.show',
                'customer.portal.login',
            ] as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                'Missing route: '.$routeName,
            );
        }
    }
}
