<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class KarwanHomepageServiceCardLayoutFixTest extends TestCase
{
    public function test_homepage_uses_actual_premium_studio_root_and_service_contract(): void
    {
        $home = file_get_contents(
            resource_path('views/website/home.blade.php'),
        );

        $this->assertIsString($home);

        foreach (
            [
                'class="kes-premium-studio-home"',
                'id="services"',
                'class="service-bento"',
                'class="service-tile service-tile--primary',
                'class="service-tile service-tile--gold',
                'class="service-tile__number"',
                'class="service-tile__icon"',
                'class="service-tile__arrow"',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $home,
            );
        }
    }

    public function test_service_layout_fix_uses_correct_root_and_non_overlapping_grid(): void
    {
        $css = file_get_contents(
            public_path('css/karwan-premium-studio-v3.css'),
        );

        $this->assertIsString($css);

        foreach (
            [
                'PHASE 16D-4F8C SERVICE CARD LAYOUT VISUAL FIX START',
                '.kes-premium-studio-home #services .service-tile',
                'display: grid !important;',
                'grid-template-columns:',
                'grid-template-rows:',
                '.service-tile__icon',
                'position: static !important;',
                '.service-tile__number',
                '.service-tile > div',
                'padding-inline-end: 62px;',
                '.service-tile__arrow',
                'grid-row: 3;',
                'html[dir="rtl"]',
                '@media (max-width: 1080px)',
                '@media (max-width: 720px)',
                'PHASE 16D-4F8C SERVICE CARD LAYOUT VISUAL FIX END',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $css,
            );
        }

        $this->assertStringNotContainsString(
            '.home-studio-v3 #services',
            $css,
        );
    }

    public function test_all_six_service_cards_and_routes_remain_unchanged(): void
    {
        $home = file_get_contents(
            resource_path('views/website/home.blade.php'),
        );

        $this->assertIsString($home);

        foreach (
            [
                "route('website.umrah')",
                "route('website.visas')",
                "route('website.tickets')",
                "route('website.hotels')",
                "route('website.insurance')",
                "route('website.tours')",
                "__('premium-studio.umrah_packages')",
                "__('premium-studio.visit_visas')",
                "__('premium-studio.air_ticketing')",
                "__('premium-studio.hotel_booking')",
                "__('premium-studio.insurance_assistance')",
                "__('premium-studio.pakistan_tours')",
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $home,
            );
        }
    }
}
