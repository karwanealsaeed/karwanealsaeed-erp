@extends('website.layout')

@section('content')
    <main class="kes-blog-page">
        <section class="kes-blog-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-blog-hero-inner">
                <div class="kes-blog-hero-copy">
                    <nav
                        class="kes-page-v2-breadcrumb"
                        aria-label="Breadcrumb"
                    >
                        <a href="{{ route('website.home') }}">
                            {{ __('blog.home') }}
                        </a>

                        <span>/</span>

                        <span>{{ __('blog.title') }}</span>
                    </nav>

                    <span class="kes-v2-section-label">
                        {{ __('blog.eyebrow') }}
                    </span>

                    <h1>{{ __('blog.title') }}</h1>

                    <p>{{ __('blog.subtitle') }}</p>
                </div>

                <div class="kes-blog-hero-art" aria-hidden="true">
                    <span class="kes-blog-art-sheet"></span>
                    <span class="kes-blog-art-line line-one"></span>
                    <span class="kes-blog-art-line line-two"></span>
                    <span class="kes-blog-art-line line-three"></span>
                    <span class="kes-blog-art-pen">✦</span>
                </div>
            </div>
        </section>

        <section class="kes-blog-content">
            <div class="kes-v2-container">
                <form
                    method="GET"
                    action="{{ route('website.blog') }}"
                    class="kes-blog-filters"
                >
                    @if (app()->isLocale('ur'))
                        <input
                            type="hidden"
                            name="lang"
                            value="ur"
                        >
                    @endif

                    <div class="kes-blog-filter-field">
                        <label for="blog-search">
                            {{ __('blog.search') }}
                        </label>

                        <input
                            id="blog-search"
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="{{ __('blog.search_placeholder') }}"
                        >
                    </div>

                    <div class="kes-blog-filter-field">
                        <label for="blog-category">
                            {{ __('blog.category') }}
                        </label>

                        <select
                            id="blog-category"
                            name="category"
                        >
                            <option value="">
                                {{ __('blog.all_categories') }}
                            </option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category }}"
                                    @selected(
                                        $selectedCategory === $category
                                    )
                                >
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="kes-blog-filter-actions">
                        <button
                            type="submit"
                            class="kes-v2-btn kes-v2-btn-gold"
                        >
                            {{ __('blog.filter') }}
                        </button>

                        <a
                            href="{{ route(
                                'website.blog',
                                app()->isLocale('ur')
                                    ? ['lang' => 'ur']
                                    : [],
                            ) }}"
                            class="kes-blog-clear"
                        >
                            {{ __('blog.clear_filters') }}
                        </a>
                    </div>
                </form>

                <div class="kes-blog-results-heading">
                    <div>
                        <span class="kes-v2-section-label">
                            {{ __('blog.eyebrow') }}
                        </span>

                        <h2>{{ __('blog.results_title') }}</h2>
                    </div>

                    <strong>
                        {{ trans_choice(
                            'blog.articles_count',
                            $blogPosts->total(),
                            ['count' => $blogPosts->total()],
                        ) }}
                    </strong>
                </div>

                <div class="kes-blog-grid">
                    @forelse ($blogPosts as $blogPost)
                        @php
                            $routeParameters = [
                                'blogPost' => $blogPost,
                            ];

                            if (app()->isLocale('ur')) {
                                $routeParameters['lang'] = 'ur';
                            }

                            $articleUrl = route(
                                'website.blog.show',
                                $routeParameters,
                            );

                            $excerpt = $blogPost->localized_excerpt
                                ?: \Illuminate\Support\Str::limit(
                                    trim(
                                        strip_tags(
                                            $blogPost->localized_content,
                                        ),
                                    ),
                                    150,
                                );
                        @endphp

                        <article
                            class="kes-blog-card {{ $blogPost->is_featured
                                ? 'is-featured'
                                : '' }}"
                        >
                            <a
                                href="{{ $articleUrl }}"
                                class="kes-blog-card-media"
                                aria-label="{{ $blogPost->localized_title }}"
                            >
                                @if (filled($blogPost->featured_image_path))
                                    <img
                                        src="{{ asset(
                                            'storage/'
                                            .$blogPost->featured_image_path,
                                        ) }}"
                                        alt="{{ $blogPost->localized_image_alt_text }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="kes-blog-card-placeholder">
                                        <span>✦</span>
                                        <small>
                                            {{ $blogPost->localized_category
                                                ?: __('blog.eyebrow') }}
                                        </small>
                                    </div>
                                @endif

                                <div class="kes-blog-card-overlay"></div>

                                @if ($blogPost->is_featured)
                                    <span class="kes-blog-featured-badge">
                                        {{ __('blog.featured') }}
                                    </span>
                                @endif

                                @if (filled($blogPost->localized_category))
                                    <span class="kes-blog-category-badge">
                                        {{ $blogPost->localized_category }}
                                    </span>
                                @endif
                            </a>

                            <div class="kes-blog-card-body">
                                <div class="kes-blog-card-meta">
                                    @if ($blogPost->published_at)
                                        <time
                                            datetime="{{ $blogPost
                                                ->published_at
                                                ->toDateString() }}"
                                        >
                                            {{ $blogPost
                                                ->published_at
                                                ->format('d M Y') }}
                                        </time>
                                    @endif

                                    <span>
                                        {{ __('blog.minutes_read', [
                                            'count' =>
                                                $blogPost->reading_time,
                                        ]) }}
                                    </span>
                                </div>

                                <h2>
                                    <a href="{{ $articleUrl }}">
                                        {{ $blogPost->localized_title }}
                                    </a>
                                </h2>

                                <p>{{ $excerpt }}</p>

                                <div class="kes-blog-card-footer">
                                    <span>
                                        {{ $blogPost->author_name
                                            ?: 'Karwan E Al Saeed Team' }}
                                    </span>

                                    <a href="{{ $articleUrl }}">
                                        {{ __('blog.read_article') }}

                                        <span aria-hidden="true">
                                            {{ app()->isLocale('ur')
                                                ? '←'
                                                : '→' }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="kes-blog-empty">
                            <span>✦</span>

                            <h2>{{ __('blog.no_items_title') }}</h2>

                            <p>{{ __('blog.no_items_text') }}</p>
                        </div>
                    @endforelse
                </div>

                @if ($blogPosts->hasPages())
                    <div class="kes-blog-pagination">
                        {{ $blogPosts->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>
@endsection