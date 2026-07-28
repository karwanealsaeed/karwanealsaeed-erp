<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KarwanCinematicHomepageTest extends TestCase
{
    public function test_homepage_blade_uses_approved_premium_studio_v3_contract(): void
    {
        $home = file_get_contents(
            resource_path(
                'views/website/home.blade.php',
            ),
        );

        $layout = file_get_contents(
            resource_path(
                'views/website/home-v3-layout.blade.php',
            ),
        );

        $english = require lang_path('en/premium-studio.php');

        $this->assertIsString($home);
        $this->assertIsString($layout);
        $this->assertIsArray($english);

        foreach (
            [
                "@extends('website.home-v3-layout')",
                'لَبَّيْكَ اللَّهُمَّ لَبَّيْكَ',
                'console-card__icon',
                'service-bento',
                'process-grid',
                "route('website.umrah')",
                "route('website.services')",
                "route('website.contact')",
                "route('customer.portal.login')",
                "__('premium-studio.hero_title_prefix')",
                "__('premium-studio.hero_title_highlight')",
                "__('premium-studio.begin_journey')",
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $home,
            );
        }

        foreach (
            [
                'A journey of faith,',
                'designed with care.',
                'Begin your journey',
                'Agent Portal',
                'Premium Umrah & Travel Services',
                'Professional service, with a human touch.',
                'Everything your journey needs, in one place.',
                'From first message to final confirmation.',
            ] as $marker
        ) {
            $this->assertContains(
                $marker,
                $english,
            );
        }

        foreach (
            [
                'Your Trusted Partner for',
                'Plan Your Journey With Confidence',
                'Customer Portal',
                'dashboard.html',
                'forms.html',
                'Static preview only',
            ] as $marker
        ) {
            $this->assertStringNotContainsString(
                $marker,
                $home.$layout,
            );
            $this->assertNotContains(
                $marker,
                $english,
            );
        }
    }

    public function test_real_named_routes_and_internal_portal_contract_exist(): void
    {
        foreach (
            [
                'website.home',
                'website.umrah',
                'website.services',
                'website.contact',
                'website.language',
                'customer.portal.login',
                'customer.portal.dashboard',
                'customer.portal.documents',
                'customer.portal.ledger',
                'customer.portal.support',
            ] as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                'Missing internal route: '.$routeName,
            );
        }

        foreach (
            [
                route('website.home'),
                route('website.umrah'),
                route('website.services'),
                route('website.contact'),
                route('customer.portal.login'),
            ] as $url
        ) {
            $this->assertIsString($url);
            $this->assertNotSame('', trim($url));
        }
    }

    public function test_dedicated_home_layout_preserves_seo_pwa_ai_and_v3_assets(): void
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

        foreach (
            [
                "asset('manifest.webmanifest')",
                'canonical',
                'hreflang',
                'application/ld+json',
                "@include('components.karwan-ai-widget')",
                'serviceWorker',
                "asset('css/karwan-premium-studio-v3.css')",
                "asset('js/karwan-premium-studio-v3.js')",
                "route('customer.portal.login')",
                'premium-studio.agent_portal',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $layout,
            );
        }

        $this->assertStringContainsString(
            "@extends('website.home-v3-layout')",
            $home,
        );
        $this->assertStringContainsString(
            'لَبَّيْكَ اللَّهُمَّ لَبَّيْكَ',
            $home,
        );
        $this->assertStringContainsString(
            'console-card__icon',
            $home,
        );

        foreach (
            [
                'dashboard.html',
                'forms.html',
                'Static preview only',
                'Customer Portal',
            ] as $marker
        ) {
            $this->assertStringNotContainsString(
                $marker,
                $layout.$home,
            );
        }
    }

    public function test_english_and_urdu_v3_translation_contracts_are_complete(): void
    {
        $english = require lang_path('en/premium-studio.php');
        $urdu = require lang_path('ur/premium-studio.php');

        $this->assertIsArray($english);
        $this->assertIsArray($urdu);
        $this->assertSame(
            array_keys($english),
            array_keys($urdu),
        );

        foreach (
            [
                'hero_title_prefix',
                'hero_title_highlight',
                'begin_journey',
                'agent_portal',
                'connected_title',
                'footer_note',
            ] as $key
        ) {
            $this->assertArrayHasKey($key, $english);
            $this->assertNotSame('', trim((string) $english[$key]));
            $this->assertNotSame('', trim((string) $urdu[$key]));
        }
    }
}
