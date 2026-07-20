<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTestimonialController extends Controller
{
    public function index(Request $request): View
    {
        $selectedService = trim(
            $request->string('service')->toString(),
        );

        $selectedRating = $request->integer('rating');

        if ($selectedRating < 1 || $selectedRating > 5) {
            $selectedRating = 0;
        }

        $testimonials = Testimonial::query()
            ->published()
            ->when(
                filled($selectedService),
                fn ($query) => $query->where(
                    'service_type',
                    $selectedService,
                ),
            )
            ->when(
                $selectedRating > 0,
                fn ($query) => $query->where(
                    'rating',
                    $selectedRating,
                ),
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $serviceTypes = Testimonial::query()
            ->published()
            ->whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type')
            ->values();

        $canonical = route('website.testimonials');

        $itemListElements = $testimonials
            ->getCollection()
            ->values()
            ->map(
                fn (
                    Testimonial $testimonial,
                    int $index,
                ): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $testimonial
                        ->localized_customer_name,
                ],
            )
            ->all();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => __('testimonials.meta_title'),
            'description' => __(
                'testimonials.meta_description',
            ),
            'url' => $canonical,
            'numberOfItems' => $testimonials->total(),
            'itemListElement' => $itemListElements,
        ];

        return view('website.testimonials', [
            'company' => CompanySetting::current(),
            'testimonials' => $testimonials,
            'serviceTypes' => $serviceTypes,
            'selectedService' => $selectedService,
            'selectedRating' => $selectedRating,
            'title' => __('testimonials.meta_title'),
            'description' => __(
                'testimonials.meta_description',
            ),
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
}
