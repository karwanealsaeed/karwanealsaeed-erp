<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_listing_page_opens(): void
    {
        $response = $this->get(route('website.gallery'));

        $response
            ->assertOk()
            ->assertSee('Explore Our Gallery');
    }

    public function test_listing_only_displays_current_published_items(): void
    {
        $publishedItem = $this->createGalleryItem([
            'title' => 'Published Umrah Gallery',
            'status' => 'Published',
        ]);

        $draftItem = $this->createGalleryItem([
            'title' => 'Draft Pakistan Tour Gallery',
            'status' => 'Draft',
        ]);
        $futureItem = $this->createGalleryItem([
            'title' => 'Future Scheduled Gallery',
            'status' => 'Published',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(route('website.gallery'));

        $response
            ->assertOk()
            ->assertSee($publishedItem->title)
            ->assertDontSee($draftItem->title)
            ->assertDontSee($futureItem->title);
    }

    public function test_published_gallery_detail_page_opens(): void
    {
        $galleryItem = $this->createGalleryItem([
            'title' => 'Makkah Umrah Memories',
            'category' => 'Umrah',
            'location' => 'Makkah',
            'status' => 'Published',
        ]);

        $response = $this->get(
            route('website.gallery.show', [
                'galleryItem' => $galleryItem,
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($galleryItem->title)
            ->assertSee($galleryItem->category)
            ->assertSee($galleryItem->location);
    }

    public function test_unpublished_gallery_detail_returns_not_found(): void
    {
        $galleryItem = $this->createGalleryItem([
            'title' => 'Private Draft Gallery',
            'status' => 'Draft',
        ]);

        $response = $this->get(
            route('website.gallery.show', [
                'galleryItem' => $galleryItem,
            ]),
        );

        $response->assertNotFound();
    }

    public function test_future_gallery_detail_returns_not_found(): void
    {
        $galleryItem = $this->createGalleryItem([
            'title' => 'Scheduled Future Gallery',
            'status' => 'Published',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(
            route('website.gallery.show', [
                'galleryItem' => $galleryItem,
            ]),
        );

        $response->assertNotFound();
    }

    public function test_listing_filters_gallery_by_category(): void
    {
        $umrahItem = $this->createGalleryItem([
            'title' => 'Umrah Group Departure',
            'category' => 'Umrah',
        ]);

        $tourItem = $this->createGalleryItem([
            'title' => 'Hunza Tour Memories',
            'category' => 'Pakistan Tours',
        ]);

        $response = $this->get(
            route('website.gallery', [
                'category' => 'Umrah',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($umrahItem->title)
            ->assertDontSee($tourItem->title);
    }

    public function test_listing_filters_gallery_by_media_type(): void
    {
        $imageItem = $this->createGalleryItem([
            'title' => 'Umrah Group Image',
            'media_type' => 'Image',
        ]);

        $videoItem = $this->createGalleryItem([
            'title' => 'Umrah Group Video',
            'media_type' => 'Video',
            'image_path' => null,
            'thumbnail_path' =>
                'gallery/thumbnails/test-video.jpg',
            'video_url' =>
                'https://www.youtube.com/watch?v=test',
        ]);

        $response = $this->get(
            route('website.gallery', [
                'type' => 'Video',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($videoItem->title)
            ->assertDontSee($imageItem->title);
    }

    public function test_urdu_listing_displays_urdu_gallery_title(): void
    {
        $galleryItem = $this->createGalleryItem([
            'title' => 'Umrah Travel Memories',
            'title_ur' => 'عمرہ سفر کی یادیں',
        ]);

        $response = $this->get(
            route('website.gallery', [
                'lang' => 'ur',
            ]),
        );

        $response
            ->assertOk()
            ->assertSee($galleryItem->title_ur);
    }

    public function test_sitemap_includes_only_published_gallery_items(): void
    {
        $publishedItem = $this->createGalleryItem([
            'title' => 'Published Sitemap Gallery',
            'status' => 'Published',
        ]);

        $draftItem = $this->createGalleryItem([
            'title' => 'Draft Sitemap Gallery',
            'status' => 'Draft',
        ]);

        $futureItem = $this->createGalleryItem([
            'title' => 'Future Sitemap Gallery',
            'status' => 'Published',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/xml; charset=UTF-8',
            )
            ->assertSee(
                route('website.gallery.show', [
                    'galleryItem' => $publishedItem,
                ]),
                false,
            )
            ->assertDontSee(
                route('website.gallery.show', [
                    'galleryItem' => $draftItem,
                ]),
                false,
            )
            ->assertDontSee(
                route('website.gallery.show', [
                    'galleryItem' => $futureItem,
                ]),
                false,
            );
    }

    private function createGalleryItem(
        array $attributes = [],
    ): GalleryItem {
        return GalleryItem::create(array_merge([
            'title' => 'Gallery Test Item',
            'title_ur' => 'گیلری ٹیسٹ آئٹم',
            'category' => 'Umrah',
            'category_ur' => 'عمرہ',
            'album' => 'Travel Memories 2026',
            'album_ur' => 'سفری یادیں 2026',
            'media_type' => 'Image',
            'image_path' =>
                'gallery/images/test-gallery.jpg',
            'alt_text' => 'Gallery test image',
            'alt_text_ur' => 'گیلری ٹیسٹ تصویر',
            'caption' => 'A memorable travel experience.',
            'caption_ur' => 'ایک یادگار سفری تجربہ۔',
            'location' => 'Makkah',
            'location_ur' => 'مکہ مکرمہ',
            'event_date' => '2026-07-01',
            'photographer' => 'KES Media Team',
            'status' => 'Published',
            'is_featured' => true,
            'sort_order' => 1,
        ], $attributes));
    }
}