<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\CompanySetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicBlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategory = trim(
            $request->string('category')->toString(),
        );

        $search = trim(
            $request->string('search')->toString(),
        );

        $blogPosts = BlogPost::query()
            ->published()
            ->when(
                filled($selectedCategory),
                fn (Builder $query) => $query->where(
                    'category',
                    $selectedCategory,
                ),
            )
            ->when(
                filled($search),
                function (Builder $query) use ($search): void {
                    $query->where(
                        function (Builder $query) use ($search): void {
                            $query
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere(
                                    'title_ur',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'excerpt',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'excerpt_ur',
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

        $categories = BlogPost::query()
            ->published()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        $canonical = route('website.blog');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => __('blog.meta_title'),
            'description' => __('blog.meta_description'),
            'url' => $canonical,
            'numberOfItems' => $blogPosts->total(),
            'itemListElement' => $blogPosts
                ->getCollection()
                ->values()
                ->map(
                    fn (
                        BlogPost $blogPost,
                        int $index,
                    ): array => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $blogPost->localized_title,
                        'url' => route(
                            'website.blog.show',
                            [
                                'blogPost' => $blogPost,
                            ],
                        ),
                    ],
                )
                ->all(),
        ];

        return view('website.blog', [
            'company' => CompanySetting::current(),
            'blogPosts' => $blogPosts,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'title' => __('blog.meta_title'),
            'description' => __('blog.meta_description'),
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

    public function show(BlogPost $blogPost): View
    {
        $blogPost = BlogPost::query()
            ->published()
            ->whereKey($blogPost->getKey())
            ->firstOrFail();

        $blogPost->increment('view_count');
        $blogPost->refresh();

        $relatedPosts = BlogPost::query()
            ->published()
            ->whereKeyNot($blogPost->getKey())
            ->when(
                filled($blogPost->category),
                fn (Builder $query) => $query->where(
                    'category',
                    $blogPost->category,
                ),
            )
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $canonical = route(
            'website.blog.show',
            [
                'blogPost' => $blogPost,
            ],
        );

        $descriptionSource =
            $blogPost->localized_seo_description
            ?: $blogPost->localized_excerpt
            ?: $blogPost->localized_content;

        $description = Str::limit(
            trim(
                strip_tags(
                    (string) $descriptionSource,
                ),
            ),
            160,
            '',
        );

        $image = filled($blogPost->featured_image_path)
            ? asset(
                'storage/'.$blogPost->featured_image_path,
            )
            : asset('images/logo.png');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $blogPost->localized_title,
            'description' => $description,
            'image' => [$image],
            'url' => $canonical,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonical,
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => $blogPost->author_name
                    ?: 'Karwan E Al Saeed Travel & Tours',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Karwan E Al Saeed Travel & Tours',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                ],
            ],
            'datePublished' => $blogPost->published_at
                ?->toIso8601String(),
            'dateModified' => $blogPost->updated_at
                ?->toIso8601String(),
        ];

        return view('website.blog-detail', [
            'company' => CompanySetting::current(),
            'blogPost' => $blogPost,
            'relatedPosts' => $relatedPosts,
            'title' => $blogPost->localized_seo_title
                .' | Karwan E Al Saeed',
            'description' => $description,
            'canonical' => $canonical,
            'seoImage' => $image,
            'seoType' => 'article',
            'seoRobots' =>
                'index, follow, max-image-preview:large',
            'seoSchema' => $schema,
        ]);
    }
}