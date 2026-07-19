<?php

namespace App\Http\Controllers;

use App\Models\PakistanTour;
use App\Models\UmrahPackage;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $staticRoutes = [
            ['name' => 'website.home', 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['name' => 'website.about', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['name' => 'website.services', 'changefreq' => 'monthly', 'priority' => '0.9'],
            ['name' => 'website.umrah', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['name' => 'website.tickets', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['name' => 'website.hotels', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['name' => 'website.visas', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['name' => 'website.consultancy', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['name' => 'website.insurance', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['name' => 'website.tours', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['name' => 'website.gallery', 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['name' => 'website.testimonials', 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['name' => 'website.faq', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['name' => 'website.blog', 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['name' => 'website.contact', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ];

        $staticUrls = collect($staticRoutes)
            ->filter(
                fn (array $item): bool => app('router')
                    ->has($item['name'])
            )
            ->map(fn (array $item): array => [
                'loc' => route($item['name']),
                'lastmod' => null,
                'changefreq' => $item['changefreq'],
                'priority' => $item['priority'],
            ]);

        $packageUrls = UmrahPackage::query()
            ->where('status', 'Available')
            ->whereNotNull('slug')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->map(fn (UmrahPackage $package): array => [
                'loc' => route('website.umrah.show', [
                    'umrahPackage' => $package->slug,
                ]),
                'lastmod' => $package->updated_at?->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);

        $tourUrls = PakistanTour::query()
            ->where('status', 'Published')
            ->whereNotNull('slug')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->map(fn (PakistanTour $tour): array => [
                'loc' => route('website.tours.show', [
                    'pakistanTour' => $tour->slug,
                ]),
                'lastmod' => $tour->updated_at?->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);

        $urls = $staticUrls
            ->concat($packageUrls)
            ->concat($tourUrls);

        return response()->view(
            'website.sitemap',
            ['urls' => $urls],
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8'],
        );
    }
}