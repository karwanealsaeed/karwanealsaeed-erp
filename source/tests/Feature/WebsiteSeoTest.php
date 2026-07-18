<?php

namespace Tests\Feature;

use App\Models\UmrahPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WebsiteSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_contains_primary_seo_metadata(): void
    {
        $response = $this->get(route('website.home'));

        $response
            ->assertOk()
            ->assertSee('<title>', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="robots"', false)
            ->assertSee(
                'rel="canonical" href="'.route('website.home').'"',
                false,
            )
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('property="og:locale" content="en_PK"', false)
            ->assertSee('name="twitter:card"', false)
            ->assertSee('"@type": "TravelAgency"', false);
    }

    public function test_sitemap_is_valid_xml_and_contains_public_pages(): void
    {
        $package = $this->createPackage();

        $response = $this->get(route('website.sitemap'));

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/xml; charset=UTF-8',
            )
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee('<urlset', false)
            ->assertSee(route('website.home'), false)
            ->assertSee(route('website.umrah'), false)
            ->assertSee(
                route('website.umrah.show', [
                    'umrahPackage' => $package,
                ]),
                false,
            );
    }

    public function test_sitemap_excludes_unavailable_packages(): void
    {
        $package = $this->createPackage([
            'package_no' => 'SEO-CLOSED-001',
            'title' => 'Unavailable SEO Package',
            'status' => 'Closed',
        ]);

        $response = $this->get(route('website.sitemap'));

        $response
            ->assertOk()
            ->assertDontSee(
                route('website.umrah.show', [
                    'umrahPackage' => $package,
                ]),
                false,
            );
    }

    public function test_umrah_listing_contains_item_list_schema(): void
    {
        $package = $this->createPackage();

        $response = $this->get(route('website.umrah'));

        $response
            ->assertOk()
            ->assertSee(
                'rel="canonical" href="'.route('website.umrah').'"',
                false,
            )
            ->assertSee('"@type": "ItemList"', false)
            ->assertSee($package->title, false)
            ->assertSee(
                route('website.umrah.show', [
                    'umrahPackage' => $package,
                ]),
                false,
            );
    }

    public function test_filtered_umrah_listing_is_noindex(): void
    {
        $response = $this->get(
            route('website.umrah', ['search' => 'Ramadan']),
        );

        $response
            ->assertOk()
            ->assertSee(
                'name="robots" content="noindex, follow, max-image-preview:large"',
                false,
            )
            ->assertSee(
                'rel="canonical" href="'.route('website.umrah').'"',
                false,
            );
    }

    public function test_umrah_detail_contains_product_and_offer_schema(): void
    {
        $package = $this->createPackage();

        $response = $this->get(
            route('website.umrah.show', [
                'umrahPackage' => $package,
            ]),
        );

        $response
            ->assertOk()
            ->assertSee(
                'rel="canonical" href="'.
                route('website.umrah.show', [
                    'umrahPackage' => $package,
                ]).
                '"',
                false,
            )
            ->assertSee('property="og:type" content="product"', false)
            ->assertSee('"@type": "Product"', false)
            ->assertSee('"@type": "Offer"', false)
            ->assertSee('"priceCurrency": "PKR"', false)
            ->assertSee($package->title, false);
    }

    public function test_urdu_home_has_localized_canonical_and_hreflang_tags(): void
    {
        $response = $this->get(
            route('website.home', ['lang' => 'ur']),
        );

        $response
            ->assertOk()
            ->assertSee('<html lang="ur" dir="rtl">', false)
            ->assertSee(
                'rel="canonical" href="'.
                route('website.home', ['lang' => 'ur']).
                '"',
                false,
            )
            ->assertSee(
                'hreflang="en-PK" href="'.route('website.home').'"',
                false,
            )
            ->assertSee(
                'hreflang="ur-PK" href="'.
                route('website.home', ['lang' => 'ur']).
                '"',
                false,
            )
            ->assertSee(
                'hreflang="x-default" href="'.route('website.home').'"',
                false,
            )
            ->assertSee(
                'property="og:locale" content="ur_PK"',
                false,
            )
            ->assertSee(
                'property="og:locale:alternate" content="en_PK"',
                false,
            );
    }

    public function test_urdu_umrah_listing_is_indexable(): void
    {
        $response = $this->get(
            route('website.umrah', ['lang' => 'ur']),
        );

        $response
            ->assertOk()
            ->assertSee(
                'name="robots" content="index, follow',
                false,
            )
            ->assertSee(
                'rel="canonical" href="'.
                route('website.umrah', ['lang' => 'ur']).
                '"',
                false,
            );
    }

    public function test_filtered_urdu_umrah_listing_is_noindex(): void
    {
        $response = $this->get(
            route('website.umrah', [
                'lang' => 'ur',
                'search' => 'Ramadan',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee(
                'name="robots" content="noindex, follow',
                false,
            )
            ->assertSee(
                'rel="canonical" href="'.
                route('website.umrah', ['lang' => 'ur']).
                '"',
                false,
            );
    }

    public function test_robots_file_protects_private_areas_and_lists_sitemap(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertIsString($robots);
        $this->assertStringContainsString('Allow: /', $robots);
        $this->assertStringContainsString('Disallow: /admin/', $robots);
        $this->assertStringContainsString('Disallow: /customer/', $robots);
        $this->assertStringContainsString(
            'Sitemap: https://karwanealsaeed.com/sitemap.xml',
            $robots,
        );
    }

    private function createPackage(array $overrides = []): UmrahPackage
    {
        $attributes = [
            'package_no' => 'SEO-AVAILABLE-001',
            'title' => 'Ramadan Umrah SEO Package',
            'subtitle' => 'Complete Umrah package for SEO testing.',
            'description' => 'Flights, hotels and transport are included.',
            'status' => 'Available',
            'price' => 125000,
            'is_featured' => true,
            'sort_order' => 1,
        ];

        if (Schema::hasColumn('umrah_packages', 'sharing_price')) {
            $attributes['sharing_price'] = 125000;
        }

        return UmrahPackage::query()->create(
            array_merge($attributes, $overrides),
        );
    }
}