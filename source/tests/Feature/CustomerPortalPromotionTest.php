<?php

namespace Tests\Feature;

use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CustomerPortalPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_promotions_route_is_registered(): void
    {
        $this->assertTrue(
            Route::has('customer.portal.promotions'),
        );
    }

    public function test_customer_promotions_require_portal_login(): void
    {
        $response = $this->get(
            route('customer.portal.promotions'),
        );

        $response->assertRedirect(
            route('customer.portal.login'),
        );
    }

    public function test_dashboard_component_only_displays_active_offers(): void
    {
        $active = $this->createPromotion([
            'title' => 'Customer Active Offer',
        ]);

        $this->createPromotion([
            'title' => 'Customer Expired Offer',
            'ends_at' => now()->subMinute(),
        ]);

        $html = Blade::render(
            '<x-customer-latest-offers />',
        );

        $this->assertStringContainsString(
            $active->title,
            $html,
        );

        $this->assertStringNotContainsString(
            'Customer Expired Offer',
            $html,
        );
    }

    private function createPromotion(
        array $attributes = [],
    ): Promotion {
        return Promotion::query()->create(
            array_merge(
                [
                    'title' => 'Customer Portal Offer',
                    'subtitle' => 'Active customer offer',
                    'promotion_type' => 'Travel',
                    'promo_code' => 'PORTAL2026',
                    'status' => 'Published',
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addDay(),
                    'published_at' => now()->subMinute(),
                    'is_featured' => true,
                    'sort_order' => 0,
                ],
                $attributes,
            ),
        );
    }
}