<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function home(): View
    {
        return view('website.home', [
            'company' => CompanySetting::current(),
            'title' => 'Karwan E Al Saeed Travel & Tours',
            'description' => __('home.hero_text'),
        ]);
    }

    public function page(string $slug): View
    {
        $pages = $this->pages();

        abort_unless(array_key_exists($slug, $pages), 404);

        $page = $pages[$slug];

        return view('website.page', [
            'company' => CompanySetting::current(),
            'page' => $page,
            'slug' => $slug,
            'title' => $page['title'].' | Karwan E Al Saeed',
            'description' => $page['description'],
        ]);
    }

    /**
     * Return public website pages in the currently selected language.
     */
    private function pages(): array
    {
        $pages = trans('pages');

        if (! is_array($pages)) {
            return [];
        }

        return collect($pages)
            ->map(function (array $page): array {
                /*
                 * Keep the existing Blade template compatible while
                 * multilingual page styling is completed.
                 */
                $page['urdu'] = $page['secondary'] ?? '';

                return $page;
            })
            ->all();
    }
}