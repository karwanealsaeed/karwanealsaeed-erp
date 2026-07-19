<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\PakistanTour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicPakistanTourController extends Controller
{
    public function index(Request $request): View
    {
        $toursQuery = PakistanTour::query()
            ->where('status', 'Published');

        $toursQuery
            ->when(
                $request->filled('search'),
                function ($query) use ($request): void {
                    $search = $request
                        ->string('search')
                        ->toString();

                    $query->where(
                        function ($subQuery) use ($search): void {
                            $subQuery
                                ->where(
                                    'title',
                                    'like',
                                    '%'.$search.'%',
                                )
                                ->orWhere(
                                    'title_ur',
                                    'like',
                                    '%'.$search.'%',
                                )
                                ->orWhere(
                                    'subtitle',
                                    'like',
                                    '%'.$search.'%',
                                )
                                ->orWhere(
                                    'subtitle_ur',
                                    'like',
                                    '%'.$search.'%',
                                )
                                ->orWhere(
                                    'destination',
                                    'like',
                                    '%'.$search.'%',
                                )
                                ->orWhere(
                                    'province',
                                    'like',
                                    '%'.$search.'%',
                                );
                        },
                    );
                },
            )
            ->when(
                $request->filled('province'),
                function ($query) use ($request): void {
                    $query->where(
                        'province',
                        $request
                            ->string('province')
                            ->toString(),
                    );
                },
            )
            ->when(
                $request->filled('duration'),
                function ($query) use ($request): void {
                    $duration = $request
                        ->string('duration')
                        ->toString();

                    if ($duration === '1-3') {
                        $query->whereBetween(
                            'duration_days',
                            [1, 3],
                        );
                    }

                    if ($duration === '4-7') {
                        $query->whereBetween(
                            'duration_days',
                            [4, 7],
                        );
                    }

                    if ($duration === '8+') {
                        $query->where(
                            'duration_days',
                            '>=',
                            8,
                        );
                    }
                },
            );

        match ($request->string('sort')->toString()) {
            'price_low' => $toursQuery->orderBy('price'),
            'price_high' => $toursQuery->orderByDesc('price'),
            'duration_low' => $toursQuery->orderBy('duration_days'),
            'latest' => $toursQuery->orderByDesc('id'),
            default => $toursQuery
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('id'),
        };

        $tours = $toursQuery
            ->paginate(12)
            ->withQueryString();

        $provinces = PakistanTour::query()
            ->where('status', 'Published')
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        $canonical = route('website.tours');

        $itemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => __('tours.seo_listing_title'),
            'url' => $canonical,
            'numberOfItems' => $tours->count(),
            'itemListElement' => $tours
                ->getCollection()
                ->values()
                ->map(
                    fn (
                        PakistanTour $tour,
                        int $index,
                    ): array => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $tour->localized_title,
                        'url' => route(
                            'website.tours.show',
                            [
                                'pakistanTour' => $tour,
                            ],
                        ),
                    ],
                )
                ->all(),
        ];

        return view('website.pakistan-tours', [
            'company' => CompanySetting::current(),
            'tours' => $tours,
            'provinces' => $provinces,
            'title' => __('tours.seo_listing_title'),
            'description' => __(
                'tours.seo_listing_description',
            ),
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

    public function show(PakistanTour $pakistanTour): View
    {
        abort_unless(
            $pakistanTour->status === 'Published',
            404,
        );

        $company = CompanySetting::current();

        $canonical = route('website.tours.show', [
            'pakistanTour' => $pakistanTour,
        ]);

        $image = $pakistanTour->cover_image
            ? asset(
                'storage/'.$pakistanTour->cover_image,
            )
            : asset('images/logo.png');

        $metaTitle = app()->isLocale('ur')
            && filled($pakistanTour->meta_title_ur)
                ? $pakistanTour->meta_title_ur
                : (
                    $pakistanTour->meta_title
                    ?: $pakistanTour->localized_title
                );

        $metaDescription = app()->isLocale('ur')
            && filled($pakistanTour->meta_description_ur)
                ? $pakistanTour->meta_description_ur
                : $pakistanTour->meta_description;

        $description = Str::limit(
            trim(
                strip_tags(
                    $metaDescription
                    ?: $pakistanTour
                        ->localized_short_description
                    ?: $pakistanTour
                        ->localized_description
                    ?: __('tours.seo_detail_fallback_description'),
                ),
            ),
            160,
            '',
        );

        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $pakistanTour->localized_title,
            'description' => $description,
            'url' => $canonical,
            'image' => [$image],
            'sku' => $pakistanTour->tour_no,
            'category' => 'Pakistan Tour Package',
            'brand' => [
                '@type' => 'Brand',
                'name' => $company->company_name,
            ],
        ];

        if ((float) $pakistanTour->price > 0) {
            $productSchema['offers'] = [
                '@type' => 'Offer',
                'url' => $canonical,
                'priceCurrency' => 'PKR',
                'price' => number_format(
                    (float) $pakistanTour->price,
                    2,
                    '.',
                    '',
                ),
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@type' => 'TravelAgency',
                    'name' => $company->company_name,
                ],
            ];
        }

        return view('website.pakistan-tour-detail', [
            'company' => $company,
            'tour' => $pakistanTour,
            'title' => $metaTitle
                .' | Karwan E Al Saeed',
            'description' => $description,
            'canonical' => $canonical,
            'seoImage' => $image,
            'seoType' => 'product',
            'seoSchema' => $productSchema,
        ]);
    }
}