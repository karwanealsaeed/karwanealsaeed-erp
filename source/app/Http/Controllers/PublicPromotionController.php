<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicPromotionController extends Controller
{
    public function index(Request $request): View
    {
        $selectedType = trim(
            $request->string('type')->toString(),
        );

        $search = trim(
            $request->string('search')->toString(),
        );

        $promotions = Promotion::query()
            ->active()
            ->when(
                filled($selectedType),
                fn (Builder $query) => $query->where(
                    'promotion_type',
                    $selectedType,
                ),
            )
            ->when(
                filled($search),
                function (Builder $query) use ($search): void {
                    $query->where(
                        function (Builder $query) use ($search): void {
                            $query
                                ->where(
                                    'title',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'title_ur',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'subtitle',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'subtitle_ur',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'description_ur',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'promo_code',
                                    'like',
                                    "%{$search}%",
                                );
                        },
                    );
                },
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        $promotionTypes = Promotion::query()
            ->active()
            ->whereNotNull('promotion_type')
            ->where('promotion_type', '!=', '')
            ->distinct()
            ->orderBy('promotion_type')
            ->pluck('promotion_type')
            ->values();

        $canonical = route('website.promotions');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => __('promotions.meta_title'),
            'description' => __('promotions.meta_description'),
            'url' => $canonical,
            'numberOfItems' => $promotions->total(),
            'itemListElement' => $promotions
                ->getCollection()
                ->values()
                ->map(
                    fn (
                        Promotion $promotion,
                        int $index,
                    ): array => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $promotion->localized_title,
                        'url' => route(
                            'website.promotions.show',
                            [
                                'promotion' => $promotion,
                            ],
                        ),
                    ],
                )
                ->all(),
        ];

        return view('website.promotions', [
            'company' => CompanySetting::current(),
            'promotions' => $promotions,
            'promotionTypes' => $promotionTypes,
            'selectedType' => $selectedType,
            'search' => $search,
            'title' => __('promotions.meta_title'),
            'description' => __('promotions.meta_description'),
            'canonical' => $canonical,
            'seoImage' => asset('images/logo.png'),
            'seoType' => 'website',
            'seoRobots' => collect($request->query())
                ->except('lang')
                ->isNotEmpty()
                    ? 'noindex, follow, max-image-preview:large'
                    : 'index, follow, max-image-preview:large, max-snippet:-1',
            'seoSchema' => $schema,
        ]);
    }

    public function show(Promotion $promotion): View
    {
        $promotion = Promotion::query()
            ->active()
            ->whereKey($promotion->getKey())
            ->firstOrFail();

        $relatedPromotions = Promotion::query()
            ->active()
            ->whereKeyNot($promotion->getKey())
            ->when(
                filled($promotion->promotion_type),
                fn (Builder $query) => $query->where(
                    'promotion_type',
                    $promotion->promotion_type,
                ),
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $canonical = route(
            'website.promotions.show',
            [
                'promotion' => $promotion,
            ],
        );

        $description = Str::limit(
            trim(
                strip_tags(
                    (string) (
                        $promotion->localized_description
                        ?: $promotion->localized_subtitle
                        ?: $promotion->localized_title
                    ),
                ),
            ),
            160,
            '',
        );

        $image = filled($promotion->featured_image_path)
            ? asset(
                'storage/'.$promotion->featured_image_path,
            )
            : asset('images/logo.png');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Offer',
            'name' => $promotion->localized_title,
            'description' => $description,
            'url' => $canonical,
            'image' => $image,
            'availability' => 'https://schema.org/InStock',
            'validFrom' => $promotion->starts_at
                ?->toIso8601String(),
            'validThrough' => $promotion->ends_at
                ?->toIso8601String(),
            'seller' => [
                '@type' => 'TravelAgency',
                'name' => 'Karwan E Al Saeed Travel & Tours',
            ],
        ];

        if (filled($promotion->offer_price)) {
            $schema['price'] = (string) $promotion->offer_price;
            $schema['priceCurrency'] =
                $promotion->currency ?: 'PKR';
        }

        return view('website.promotion-detail', [
            'company' => CompanySetting::current(),
            'promotion' => $promotion,
            'relatedPromotions' => $relatedPromotions,
            'title' => $promotion->localized_title
                .' | Karwan E Al Saeed',
            'description' => $description,
            'canonical' => $canonical,
            'seoImage' => $image,
            'seoType' => 'product',
            'seoRobots' =>
                'index, follow, max-image-preview:large',
            'seoSchema' => $schema,
        ]);
    }
}