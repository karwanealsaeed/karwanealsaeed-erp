<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    private const PAGES = [
        'privacy-policy' => ['route' => 'website.legal.privacy', 'key' => 'privacy'],
        'terms-and-conditions' => ['route' => 'website.legal.terms', 'key' => 'terms'],
        'refund-policy' => ['route' => 'website.legal.refund', 'key' => 'refund'],
        'cancellation-policy' => ['route' => 'website.legal.cancellation', 'key' => 'cancellation'],
        'disclaimer' => ['route' => 'website.legal.disclaimer', 'key' => 'disclaimer'],
    ];

    public function show(string $slug): View
    {
        abort_unless(array_key_exists($slug, self::PAGES), 404);

        $configuration = self::PAGES[$slug];
        $page = __('legal.pages.'.$configuration['key']);

        abort_unless(is_array($page), 404);

        $parameters = app()->isLocale('ur')
            ? ['lang' => 'ur']
            : [];

        $canonical = route($configuration['route'], $parameters);
        $company = CompanySetting::current();

        return view('website.legal-page', [
            'company' => $company,
            'page' => $page,
            'legalKey' => $configuration['key'],
            'title' => $page['title'].' | '.$company->company_name,
            'description' => $page['description'],
            'canonical' => $canonical,
            'seoImage' => asset('images/logo.png'),
            'seoType' => 'website',
            'seoRobots' => 'index, follow, max-image-preview:large, max-snippet:-1',
            'seoSchema' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $page['title'],
                'description' => $page['description'],
                'url' => $canonical,
                'dateModified' => '2026-07-22',
                'publisher' => [
                    '@type' => 'TravelAgency',
                    'name' => $company->company_name,
                    'url' => $company->website ?: route('website.home'),
                ],
            ],
        ]);
    }
}
