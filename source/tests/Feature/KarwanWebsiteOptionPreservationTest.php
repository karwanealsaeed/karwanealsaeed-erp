<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanWebsiteOptionPreservationTest extends TestCase
{
    public function test_v3_shell_contains_every_legacy_visible_route_option(): void
    {
        $layout = file_get_contents(
            resource_path(
                'views/website/home-v3-layout.blade.php',
            ),
        );

        $home = file_get_contents(
            resource_path(
                'views/website/home.blade.php',
            ),
        );

        $this->assertIsString($layout);
        $this->assertIsString($home);

        $combined = $layout.$home;

        foreach (
            [
                'customer.portal.login',
                'website.about',
                'website.blog',
                'website.contact',
                'website.faq',
                'website.gallery',
                'website.home',
                'website.hotels',
                'website.insurance',
                'website.language',
                'website.legal.cancellation',
                'website.legal.disclaimer',
                'website.legal.privacy',
                'website.legal.refund',
                'website.legal.terms',
                'website.promotions',
                'website.services',
                'website.tickets',
                'website.tours',
                'website.umrah',
                'website.visas',
            ] as $routeName
        ) {
            $this->assertStringContainsString(
                $routeName,
                $combined,
                'V3 shell missing visible option: '.$routeName,
            );
        }
    }

    public function test_all_public_and_agent_portal_route_contracts_still_exist(): void
    {
        foreach (
            [
                'website.home',
                'website.about',
                'website.services',
                'website.umrah',
                'website.umrah.show',
                'website.visas',
                'website.tickets',
                'website.hotels',
                'website.insurance',
                'website.tours',
                'website.tours.show',
                'website.consultancy',
                'website.promotions',
                'website.promotions.show',
                'website.gallery',
                'website.gallery.show',
                'website.blog',
                'website.blog.show',
                'website.testimonials',
                'website.faq',
                'website.contact',
                'website.legal.privacy',
                'website.legal.refund',
                'website.legal.cancellation',
                'website.legal.disclaimer',
                'website.legal.terms',
                'website.language',
                'website.sitemap',
                'customer.portal.login',
                'customer.portal.register',
                'customer.portal.dashboard',
                'customer.portal.documents',
                'customer.portal.flights',
                'customer.portal.hotels',
                'customer.portal.invoices',
                'customer.portal.ledger',
                'customer.portal.notifications',
                'customer.portal.payment_uploads',
                'customer.portal.profile',
                'customer.portal.promotions',
                'customer.portal.support',
                'customer.portal.tracking',
                'customer.portal.travel_insurance',
                'customer.portal.umrah',
                'customer.portal.visa',
                'customer.portal.vouchers',
            ] as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                'Missing route contract: '.$routeName,
            );
        }
    }

    public function test_v3_shell_preserves_language_seo_pwa_ai_and_agent_portal(): void
    {
        $layout = file_get_contents(
            resource_path(
                'views/website/home-v3-layout.blade.php',
            ),
        );

        $this->assertIsString($layout);

        foreach (
            [
                "route('website.language'",
                "route('customer.portal.login')",
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
                $layout,
            );
        }

        $this->assertStringNotContainsString(
            'Customer Portal',
            $layout,
        );
    }

    public function test_all_four_restored_legal_labels_exist_in_english_and_urdu(): void
    {
        $english = require lang_path('en/premium-studio.php');
        $urdu = require lang_path('ur/premium-studio.php');

        $this->assertSame(
            array_keys($english),
            array_keys($urdu),
        );

        foreach (
            [
                'privacy',
                'refund',
                'cancellation',
                'disclaimer',
                'terms',
            ] as $key
        ) {
            $this->assertArrayHasKey($key, $english);
            $this->assertArrayHasKey($key, $urdu);
            $this->assertNotSame('', trim((string) $english[$key]));
            $this->assertNotSame('', trim((string) $urdu[$key]));
        }
    }

    public function test_inner_public_views_follow_the_controlled_v3_migration_state(): void
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
