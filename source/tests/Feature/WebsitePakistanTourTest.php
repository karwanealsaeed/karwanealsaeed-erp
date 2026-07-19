<?php

namespace Tests\Feature;

use App\Models\PakistanTour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsitePakistanTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_pakistan_tours_listing_page_opens(): void
    {
        $response = $this->get(route('website.tours'));

        $response
            ->assertOk()
            ->assertSee('Pakistan Tour Packages');
    }

    public function test_listing_only_displays_published_tours(): void
    {
        $publishedTour = $this->createTour([
            'title' => 'Hunza Published Tour',
            'status' => 'Published',
        ]);

        $draftTour = $this->createTour([
            'title' => 'Skardu Draft Tour',
            'status' => 'Draft',
        ]);

        $response = $this->get(route('website.tours'));

        $response
            ->assertOk()
            ->assertSee($publishedTour->title)
            ->assertDontSee($draftTour->title);
    }

    public function test_published_tour_detail_page_opens(): void
    {
        $tour = $this->createTour([
            'title' => 'Hunza Valley Family Tour',
            'status' => 'Published',
        ]);

        $response = $this->get(
            route('website.tours.show', [
                'pakistanTour' => $tour,
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($tour->title)
            ->assertSee($tour->destination)
            ->assertSee('PKR 65,000');
    }

    public function test_unpublished_tour_detail_page_returns_not_found(): void
    {
        $tour = $this->createTour([
            'title' => 'Private Draft Tour',
            'status' => 'Draft',
        ]);

        $response = $this->get(
            route('website.tours.show', [
                'pakistanTour' => $tour,
            ]),
        );

        $response->assertNotFound();
    }

    public function test_listing_filters_tours_by_province(): void
    {
        $hunzaTour = $this->createTour([
            'title' => 'Hunza Adventure Tour',
            'province' => 'Gilgit-Baltistan',
            'status' => 'Published',
        ]);

        $murreeTour = $this->createTour([
            'title' => 'Murree Family Tour',
            'province' => 'Punjab',
            'status' => 'Published',
        ]);

        $response = $this->get(
            route('website.tours', [
                'province' => 'Gilgit-Baltistan',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($hunzaTour->title)
            ->assertDontSee($murreeTour->title);
    }

    public function test_urdu_listing_displays_urdu_tour_title(): void
    {
        $tour = $this->createTour([
            'title' => 'Hunza Valley Tour',
            'title_ur' => 'ہنزہ ویلی ٹور',
            'status' => 'Published',
        ]);

        $response = $this->get(
            route('website.tours', [
                'lang' => 'ur',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($tour->title_ur);
    }
public function test_sitemap_includes_only_published_pakistan_tours(): void
{
    $publishedTour = $this->createTour([
        'title' => 'Published Sitemap Tour',
        'status' => 'Published',
    ]);

    $draftTour = $this->createTour([
        'title' => 'Draft Sitemap Tour',
        'status' => 'Draft',
    ]);

    $response = $this->get('/sitemap.xml');

    $response
        ->assertOk()
        ->assertHeader(
            'Content-Type',
            'application/xml; charset=UTF-8',
        )
        ->assertSee(
            route('website.tours.show', [
                'pakistanTour' => $publishedTour,
            ]),
            false,
        )
        ->assertDontSee(
            route('website.tours.show', [
                'pakistanTour' => $draftTour,
            ]),
            false,
        );
}
    private function createTour(array $attributes = []): PakistanTour
    {
        return PakistanTour::create(array_merge([
            'title' => 'Pakistan Test Tour',
            'title_ur' => 'پاکستان ٹیسٹ ٹور',
            'destination' => 'Hunza Valley',
            'province' => 'Gilgit-Baltistan',
            'departure_city' => 'Multan',
            'duration_days' => 5,
            'duration_nights' => 4,
            'group_size' => 25,
            'seats_available' => 25,
            'seats_left' => 25,
            'price' => 65000,
            'short_description' => 'A complete Pakistan tour package.',
            'short_description_ur' => 'پاکستان کا مکمل ٹور پیکج۔',
            'status' => 'Published',
            'is_featured' => true,
            'sort_order' => 1,
        ], $attributes));
    }
}