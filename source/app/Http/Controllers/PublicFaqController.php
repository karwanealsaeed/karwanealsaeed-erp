<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicFaqController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategory = trim(
            $request->string('category')->toString(),
        );

        $faqs = Faq::query()
            ->published()
            ->when(
                filled($selectedCategory),
                fn ($query) => $query->where(
                    'category',
                    $selectedCategory,
                ),
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $categories = Faq::query()
            ->published()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        $canonical = route('website.faq');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'name' => __('faq.meta_title'),
            'description' => __('faq.meta_description'),
            'url' => $canonical,
            'mainEntity' => $faqs
                ->getCollection()
                ->map(
                    fn (Faq $faq): array => [
                        '@type' => 'Question',
                        'name' => $faq->localized_question,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => trim(
                                strip_tags(
                                    $faq->localized_answer,
                                ),
                            ),
                        ],
                    ],
                )
                ->values()
                ->all(),
        ];

        return view('website.faq', [
            'company' => CompanySetting::current(),
            'faqs' => $faqs,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'title' => __('faq.meta_title'),
            'description' => __('faq.meta_description'),
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