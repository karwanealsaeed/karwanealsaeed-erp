<?php

namespace Tests\Feature;

use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsitePromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotions_listing_page_opens(): void
    {
        $response = $this->get(
            route('website.promotions'),
        );

        $response
            ->assertOk()
            ->assertSeeText(
                'Promotions & Special Offers',
            );
    }

    public function test_listing_only_displays_current_active_promotions(): void
    {
        $active = $this->createPromotion([
            'title' => 'Active Umrah Offer',
        ]);

        $this->createPromotion([
            'title' => 'Draft Promotion',
            'status' => 'Draft',
            'published_at' => null,
        ]);

        $this->createPromotion([
            'title' => 'Future Promotion',
            'starts_at' => now()->addDay(),
        ]);

        $this->createPromotion([
            'title' => 'Expired Promotion',
            'ends_at' => now()->subMinute(),
        ]);

        $response = $this->get(
            route('website.promotions'),
        );

        $response
            ->assertOk()
            ->assertSeeText($active->title)
            ->assertDontSeeText('Draft Promotion')
            ->assertDontSeeText('Future Promotion')
            ->assertDontSeeText('Expired Promotion');
    }

    public function test_active_promotion_detail_page_opens(): void
    {
        $promotion = $this->createPromotion([
            'title' => 'Summer Ticket Offer',
            'description' => 'Special summer ticket discount.',
        ]);

        $response = $this->get(
            route(
                'website.promotions.show',
                ['promotion' => $promotion],
            ),
        );

        $response
            ->assertOk()
            ->assertSeeText($promotion->title)
            ->assertSeeText(
                'Special summer ticket discount.',
            );
    }

    public function test_inactive_promotions_return_not_found(): void
    {
        $draft = $this->createPromotion([
            'title' => 'Hidden Draft Promotion',
            'status' => 'Draft',
            'published_at' => null,
        ]);

        $future = $this->createPromotion([
            'title' => 'Scheduled Promotion',
            'published_at' => now()->addDay(),
        ]);

        $expired = $this->createPromotion([
            'title' => 'Old Expired Promotion',
            'ends_at' => now()->subMinute(),
        ]);

        foreach ([$draft, $future, $expired] as $promotion) {
            $this->get(
                route(
                    'website.promotions.show',
                    ['promotion' => $promotion],
                ),
            )->assertNotFound();
        }
    }

    public function test_listing_filters_promotions_by_type(): void
    {
        $this->createPromotion([
            'title' => 'Umrah Package Offer',
            'promotion_type' => 'Umrah',
        ]);

        $this->createPromotion([
            'title' => 'Visa Consultancy Offer',
            'promotion_type' => 'Visa',
        ]);

        $response = $this->get(
            route(
                'website.promotions',
                ['type' => 'Umrah'],
            ),
        );

        $response
            ->assertOk()
            ->assertSeeText('Umrah Package Offer')
            ->assertDontSeeText('Visa Consultancy Offer');
    }

    public function test_listing_searches_promotions_and_promo_codes(): void
    {
        $this->createPromotion([
            'title' => 'Air Ticket Discount',
            'promo_code' => 'FLY2026',
        ]);

        $this->createPromotion([
            'title' => 'Hotel Booking Promotion',
            'promo_code' => 'STAY2026',
        ]);

        $response = $this->get(
            route(
                'website.promotions',
                ['search' => 'FLY2026'],
            ),
        );

        $response
            ->assertOk()
            ->assertSeeText('Air Ticket Discount')
            ->assertDontSeeText('Hotel Booking Promotion');
    }

    public function test_urdu_listing_displays_localized_content(): void
    {
        $this->createPromotion([
            'title' => 'English Umrah Offer',
            'title_ur' => 'خصوصی عمرہ آفر',
            'subtitle_ur' => 'محدود مدت کے لیے رعایتی پیکیج',
        ]);

        $response = $this->get(
            route(
                'website.promotions',
                ['lang' => 'ur'],
            ),
        );

        $response
            ->assertOk()
            ->assertSeeText('خصوصی عمرہ آفر')
            ->assertSeeText(
                'محدود مدت کے لیے رعایتی پیکیج',
            );
    }

    public function test_filtered_listing_is_noindex(): void
    {
        $this->createPromotion();

        $response = $this->get(
            route(
                'website.promotions',
                ['search' => 'Offer'],
            ),
        );

        $response
            ->assertOk()
            ->assertSee(
                'noindex, follow, max-image-preview:large',
                false,
            );
    }

    public function test_sitemap_includes_only_active_promotions(): void
    {
        $active = $this->createPromotion([
            'title' => 'Active Sitemap Promotion',
        ]);

        $expired = $this->createPromotion([
            'title' => 'Expired Sitemap Promotion',
            'ends_at' => now()->subMinute(),
        ]);

        $draft = $this->createPromotion([
            'title' => 'Draft Sitemap Promotion',
            'status' => 'Draft',
            'published_at' => null,
        ]);

        $response = $this->get(
            route('website.sitemap'),
        );

        $response
            ->assertOk()
            ->assertSee(
                route(
                    'website.promotions.show',
                    ['promotion' => $active],
                ),
                false,
            )
            ->assertDontSee(
                route(
                    'website.promotions.show',
                    ['promotion' => $expired],
                ),
                false,
            )
            ->assertDontSee(
                route(
                    'website.promotions.show',
                    ['promotion' => $draft],
                ),
                false,
            );
    }
    private function createPromotion(
        array $attributes = [],
    ): Promotion {
        return Promotion::query()->create(
            array_merge(
                [
                    'title' => 'Current Travel Offer',
                    'subtitle' => 'Limited-time travel deal',
                    'description' => 'Current promotion description.',
                    'promotion_type' => 'Travel',
                    'promo_code' => 'KARWAN2026',
                    'discount_type' => 'Percentage',
                    'discount_value' => 10,
                    'currency' => 'PKR',
                    'status' => 'Published',
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addDay(),
                    'published_at' => now()->subMinute(),
                    'is_featured' => false,
                    'sort_order' => 0,
                ],
                $attributes,
            ),
        );
    }
}