<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteTestimonialTest extends TestCase
{
    use RefreshDatabase;

    public function test_testimonials_listing_page_opens(): void
    {
        $response = $this->get(
            route('website.testimonials'),
        );

        $response
            ->assertOk()
            ->assertSee('What Our Customers Say');
    }

    public function test_listing_only_displays_current_published_testimonials(): void
    {
        $published = $this->createTestimonial([
            'customer_name' => 'Published Customer',
            'status' => 'Published',
        ]);

        $draft = $this->createTestimonial([
            'customer_name' => 'Draft Customer',
            'status' => 'Draft',
        ]);

        $future = $this->createTestimonial([
            'customer_name' => 'Future Customer',
            'status' => 'Published',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(
            route('website.testimonials'),
        );

        $response
            ->assertOk()
            ->assertSee($published->customer_name)
            ->assertDontSee($draft->customer_name)
            ->assertDontSee($future->customer_name);
    }

    public function test_listing_filters_testimonials_by_service(): void
    {
        $umrah = $this->createTestimonial([
            'customer_name' => 'Umrah Customer',
            'service_type' => 'Umrah Package',
        ]);

        $ticketing = $this->createTestimonial([
            'customer_name' => 'Ticketing Customer',
            'service_type' => 'Air Ticketing',
        ]);

        $response = $this->get(
            route('website.testimonials', [
                'service' => 'Umrah Package',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($umrah->customer_name)
            ->assertDontSee($ticketing->customer_name);
    }

    public function test_listing_filters_testimonials_by_rating(): void
    {
        $fiveStar = $this->createTestimonial([
            'customer_name' => 'Five Star Customer',
            'rating' => 5,
        ]);

        $threeStar = $this->createTestimonial([
            'customer_name' => 'Three Star Customer',
            'rating' => 3,
        ]);

        $response = $this->get(
            route('website.testimonials', [
                'rating' => 5,
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($fiveStar->customer_name)
            ->assertDontSee($threeStar->customer_name);
    }

    public function test_urdu_listing_displays_localized_content(): void
    {
        $testimonial = $this->createTestimonial([
            'customer_name' => 'English Customer',
            'customer_name_ur' => 'احمد رضا',
            'testimonial' => 'English customer review.',
            'testimonial_ur' => 'بہترین سروس اور مکمل معاونت۔',
        ]);

        $response = $this->get(
            route('website.testimonials', [
                'lang' => 'ur',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($testimonial->customer_name_ur)
            ->assertSee($testimonial->testimonial_ur)
            ->assertDontSee($testimonial->testimonial);
    }

    public function test_featured_testimonials_are_displayed_first(): void
    {
        $regular = $this->createTestimonial([
            'customer_name' => 'Regular Customer',
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $featured = $this->createTestimonial([
            'customer_name' => 'Featured Customer',
            'is_featured' => true,
            'sort_order' => 100,
        ]);

        $response = $this->get(
            route('website.testimonials'),
        );

        $response
            ->assertOk()
            ->assertSeeInOrder([
                $featured->customer_name,
                $regular->customer_name,
            ]);
    }

    public function test_video_testimonial_link_is_displayed(): void
    {
        $testimonial = $this->createTestimonial([
            'customer_name' => 'Video Customer',
            'video_url' =>
                'https://example.com/customer-video',
        ]);

        $response = $this->get(
            route('website.testimonials'),
        );

        $response
            ->assertOk()
            ->assertSee($testimonial->customer_name)
            ->assertSee($testimonial->video_url, false)
            ->assertSee('Watch Customer Video');
    }

    private function createTestimonial(
        array $attributes = [],
    ): Testimonial {
        return Testimonial::create(array_merge([
            'customer_name' => 'Test Customer',
            'customer_name_ur' => 'ٹیسٹ کسٹمر',
            'designation' => 'Traveller',
            'designation_ur' => 'مسافر',
            'company_name' => 'Test Company',
            'location' => 'Multan',
            'location_ur' => 'ملتان',
            'country' => 'Pakistan',
            'rating' => 5,
            'testimonial' =>
                'Excellent service and complete support.',
            'testimonial_ur' =>
                'بہترین سروس اور مکمل معاونت۔',
            'service_type' => 'Umrah Package',
            'travel_date' => '2026-07-01',
            'status' => 'Published',
            'is_featured' => false,
            'sort_order' => 1,
            'published_at' => now()->subMinute(),
        ], $attributes));
    }
}