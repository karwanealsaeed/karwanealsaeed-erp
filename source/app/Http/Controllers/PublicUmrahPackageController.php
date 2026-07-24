<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\UmrahPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicUmrahPackageController extends Controller
{
    public function index(Request $request): View
    {
        $packagesQuery = UmrahPackage::query()
            ->with([
                'umrahGroupFlight',
                'makkahHotel',
                'madinaHotel',
                'transportRoute',
            ])
            ->publiclyAvailable();

        $packagesQuery
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('title', 'like', '%'.$search.'%')
                        ->orWhere('package_no', 'like', '%'.$search.'%')
                        ->orWhere('group_code', 'like', '%'.$search.'%')
                        ->orWhere('makkah_hotel', 'like', '%'.$search.'%')
                        ->orWhere('madina_hotel', 'like', '%'.$search.'%')
                        ->orWhere('flight_route', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('airline'), function ($query) use ($request): void {
                $query->where(
                    'airline',
                    $request->string('airline')->toString(),
                );
            })
            ->when($request->filled('route'), function ($query) use ($request): void {
                $query->where(
                    'flight_route',
                    $request->string('route')->toString(),
                );
            })
            ->when($request->filled('duration'), function ($query) use ($request): void {
                $duration = $request->string('duration')->toString();

                if ($duration === '1-10') {
                    $query->whereBetween('duration_nights', [1, 10]);
                }

                if ($duration === '11-20') {
                    $query->whereBetween('duration_nights', [11, 20]);
                }

                if ($duration === '21-30') {
                    $query->whereBetween('duration_nights', [21, 30]);
                }

                if ($duration === '31+') {
                    $query->where('duration_nights', '>=', 31);
                }
            });

        match ($request->string('sort')->toString()) {
            'price_low' => $packagesQuery->orderBy('sharing_price'),
            'price_high' => $packagesQuery->orderByDesc('sharing_price'),
            'duration_low' => $packagesQuery->orderBy('duration_nights'),
            'duration_high' => $packagesQuery->orderByDesc('duration_nights'),
            'latest' => $packagesQuery->orderByDesc('id'),
            default => $packagesQuery
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('id'),
        };

        $packages = $packagesQuery->get();

        $airlines = UmrahPackage::query()
            ->publiclyAvailable()
            ->whereNotNull('airline')
            ->distinct()
            ->orderBy('airline')
            ->pluck('airline');

        $routes = UmrahPackage::query()
            ->publiclyAvailable()
            ->whereNotNull('flight_route')
            ->distinct()
            ->orderBy('flight_route')
            ->pluck('flight_route');

        $canonical = route('website.umrah');

        $itemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => __('umrah.seo_listing_title'),
            'url' => $canonical,
            'numberOfItems' => $packages->count(),
            'itemListElement' => $packages
                ->values()
                ->map(fn (UmrahPackage $package, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $package->title,
                    'url' => route('website.umrah.show', [
                        'umrahPackage' => $package,
                    ]),
                ])
                ->all(),
        ];

        return view('website.umrah-packages', [
            'company' => CompanySetting::current(),
            'packages' => $packages,
            'airlines' => $airlines,
            'routes' => $routes,
            'title' => __('umrah.seo_listing_title'),
            'description' => __('umrah.seo_listing_description'),
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

    public function show(UmrahPackage $umrahPackage): View
    {
        $umrahPackage->load([
            'umrahGroupFlight',
            'makkahHotel',
            'madinaHotel',
            'transportRoute',
        ]);

        abort_unless($umrahPackage->isPubliclyAvailable(), 404);

        $company = CompanySetting::current();
        $canonical = route('website.umrah.show', [
            'umrahPackage' => $umrahPackage,
        ]);

        $image = $umrahPackage->package_image
            ? asset('storage/'.$umrahPackage->package_image)
            : asset('images/logo.png');

        $description = Str::limit(
            trim(strip_tags(
                $umrahPackage->subtitle
                    ?: $umrahPackage->description
                    ?: __('umrah.seo_detail_fallback_description')
            )),
            160,
            '',
        );

        $price = (float) (
            $umrahPackage->sharing_price
                ?: $umrahPackage->price
                ?: 0
        );

        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $umrahPackage->title,
            'description' => $description,
            'url' => $canonical,
            'image' => [$image],
            'sku' => $umrahPackage->package_no,
            'category' => 'Umrah Travel Package',
            'brand' => [
                '@type' => 'Brand',
                'name' => $company->company_name,
            ],
        ];

        if ($price > 0) {
            $productSchema['offers'] = [
                '@type' => 'Offer',
                'url' => $canonical,
                'priceCurrency' => 'PKR',
                'price' => number_format($price, 2, '.', ''),
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@type' => 'TravelAgency',
                    'name' => $company->company_name,
                ],
            ];
        }

        return view('website.umrah-package-detail', [
            'company' => $company,
            'package' => $umrahPackage,
            'title' => $umrahPackage->title.' | Karwan E Al Saeed',
            'description' => $description,
            'canonical' => $canonical,
            'seoImage' => $image,
            'seoType' => 'product',
            'seoSchema' => $productSchema,
        ]);
    }
}
