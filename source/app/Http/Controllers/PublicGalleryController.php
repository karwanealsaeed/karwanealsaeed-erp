<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicGalleryController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategory = trim(
            $request->string('category')->toString(),
        );

        $selectedMediaType = trim(
            $request->string('type')->toString(),
        );

        $galleryQuery = GalleryItem::query()
            ->published()
            ->when(
                filled($selectedCategory),
                fn ($query) => $query->where(
                    'category',
                    $selectedCategory,
                ),
            )
            ->when(
                in_array(
                    $selectedMediaType,
                    ['Image', 'Video'],
                    true,
                ),
                fn ($query) => $query->where(
                    'media_type',
                    $selectedMediaType,
                ),
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('event_date')
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $galleryItems = $galleryQuery
            ->paginate(12)
            ->withQueryString();

        $categories = GalleryItem::query()
            ->published()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        $canonical = route('website.gallery');

        $itemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => __('gallery.meta_title'),
            'description' => __('gallery.meta_description'),
            'url' => $canonical,
            'numberOfItems' => $galleryItems->total(),
            'itemListElement' => $galleryItems
                ->getCollection()
                ->values()
                ->map(
                    fn (
                        GalleryItem $galleryItem,
                        int $index,
                    ): array => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $galleryItem->localized_title,
                        'url' => route(
                            'website.gallery.show',
                            [
                                'galleryItem' => $galleryItem,
                            ],
                        ),
                    ],
                )
                ->all(),
        ];

        return view('website.gallery', [
            'company' => CompanySetting::current(),
            'galleryItems' => $galleryItems,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'selectedMediaType' => $selectedMediaType,
            'title' => __('gallery.meta_title'),
            'description' => __('gallery.meta_description'),
            'canonical' => $canonical,
            'seoImage' => asset('images/logo.png'),
            'seoType' => 'website',
            'seoRobots' => collect($request->query())
                ->except('lang')
                ->isNotEmpty()
                    ? 'noindex, follow, max-image-preview:large'
                    : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'seoSchema' => $itemListSchema,
        ]);
    }

    public function show(GalleryItem $galleryItem): View
    {
        $galleryItem = GalleryItem::query()
            ->published()
            ->whereKey($galleryItem->getKey())
            ->firstOrFail();

        $company = CompanySetting::current();

        $canonical = route(
            'website.gallery.show',
            [
                'galleryItem' => $galleryItem,
            ],
        );

        $descriptionSource =
            $galleryItem->localized_caption
            ?: $galleryItem->localized_category
            ?: $galleryItem->localized_title;

        $description = Str::limit(
            trim(
                strip_tags(
                    (string) $descriptionSource,
                ),
            ),
            160,
            '',
        );

        $image = filled($galleryItem->display_image)
            ? asset(
                'storage/'.$galleryItem->display_image,
            )
            : asset('images/logo.png');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $galleryItem->is_video
                ? 'VideoObject'
                : 'ImageObject',
            'name' => $galleryItem->localized_title,
            'description' => $description,
            'url' => $canonical,
        ];

        if ($galleryItem->is_video) {
            $schema['thumbnailUrl'] = [$image];

            if (filled($galleryItem->video_url)) {
                $schema['contentUrl'] =
                    $galleryItem->video_url;
            }

            if (filled($galleryItem->published_at)) {
                $schema['uploadDate'] =
                    $galleryItem
                        ->published_at
                        ->toIso8601String();
            }
        } else {
            $schema['contentUrl'] = $image;
        }

        return view('website.gallery-detail', [
            'company' => $company,
            'galleryItem' => $galleryItem,
            'title' => $galleryItem->localized_title
                .' | Karwan E Al Saeed',
            'description' => $description,
            'canonical' => $canonical,
            'seoImage' => $image,
            'seoType' => $galleryItem->is_video
                ? 'video.other'
                : 'article',
            'seoRobots' => 'index, follow, max-image-preview:large',
            'seoSchema' => $schema,
        ]);
    }
}