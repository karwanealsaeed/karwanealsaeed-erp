@extends('website.layout')

@section('content')
    @php
        $blogRouteParameters = [];

        if (app()->isLocale('ur')) {
            $blogRouteParameters['lang'] = 'ur';
        }
    @endphp

    <main class="kes-blog-detail-page">
        <section class="kes-blog-detail-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-blog-detail-hero-content">
                <nav
                    class="kes-page-v2-breadcrumb"
                    aria-label="Breadcrumb"
                >
                    <a href="{{ route('website.home') }}">
                        {{ __('blog.home') }}
                    </a>

                    <span>/</span>

                    <a
                        href="{{ route(
                            'website.blog',
                            $blogRouteParameters,
                        ) }}"
                    >
                        {{ __('blog.title') }}
                    </a>

                    <span>/</span>

                    <span>{{ $blogPost->localized_title }}</span>
                </nav>

                @if (filled($blogPost->localized_category))
                    <span class="kes-v2-section-label">
                        {{ $blogPost->localized_category }}
                    </span>
                @endif

                <h1>{{ $blogPost->localized_title }}</h1>

                @if (filled($blogPost->localized_excerpt))
                    <p class="kes-blog-detail-intro">
                        {{ $blogPost->localized_excerpt }}
                    </p>
                @endif

                <div class="kes-blog-detail-meta">
                    <span>
                        <small>{{ __('blog.author') }}</small>

                        <strong>
                            {{ $blogPost->author_name
                                ?: 'Karwan E Al Saeed Team' }}
                        </strong>
                    </span>

                    @if ($blogPost->published_at)
                        <span>
                            <small>{{ __('blog.published_on') }}</small>

                            <strong>
                                {{ $blogPost
                                    ->published_at
                                    ->format('d M Y') }}
                            </strong>
                        </span>
                    @endif

                    <span>
                        <small>Reading Time</small>

                        <strong>
                            {{ __('blog.minutes_read', [
                                'count' => $blogPost->reading_time,
                            ]) }}
                        </strong>
                    </span>

                    <span>
                        <small>Views</small>

                        <strong>
                            {{ __('blog.views', [
                                'count' => $blogPost->view_count,
                            ]) }}
                        </strong>
                    </span>
                </div>
            </div>
        </section>

        <section class="kes-blog-detail-content">
            <div class="kes-v2-container">
                @if (filled($blogPost->featured_image_path))
                    <figure class="kes-blog-detail-featured-image">
                        <img
                            src="{{ asset(
                                'storage/'
                                .$blogPost->featured_image_path,
                            ) }}"
                            alt="{{ $blogPost->localized_image_alt_text }}"
                        >
                    </figure>
                @endif

                <div class="kes-blog-detail-layout">
                    <article class="kes-blog-article">
                        <div class="kes-blog-article-body">
                            {!! $blogPost->localized_content !!}
                        </div>

                        @if (
                            is_array($blogPost->tags)
                            && count($blogPost->tags) > 0
                        )
                            <div class="kes-blog-tags">
                                <strong>
                                    {{ __('blog.article_tags') }}
                                </strong>

                                <div>
                                    @foreach ($blogPost->tags as $tag)
                                        <span>{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </article>

                    <aside class="kes-blog-detail-sidebar">
                        <div class="kes-blog-back-card">
                            <span>✦</span>

                            <h2>{{ __('blog.title') }}</h2>

                            <p>{{ __('blog.subtitle') }}</p>

                            <a
                                href="{{ route(
                                    'website.blog',
                                    $blogRouteParameters,
                                ) }}"
                                class="kes-v2-btn kes-v2-btn-gold"
                            >
                                {{ __('blog.back_to_blog') }}
                            </a>
                        </div>
                    </aside>
                </div>

                @if ($relatedPosts->isNotEmpty())
                    <section class="kes-blog-related">
                        <div class="kes-blog-related-heading">
                            <span class="kes-v2-section-label">
                                {{ __('blog.eyebrow') }}
                            </span>

                            <h2>{{ __('blog.related_articles') }}</h2>
                        </div>

                        <div class="kes-blog-related-grid">
                            @foreach ($relatedPosts as $relatedPost)
                                @php
                                    $relatedParameters = [
                                        'blogPost' => $relatedPost,
                                    ];

                                    if (app()->isLocale('ur')) {
                                        $relatedParameters['lang'] = 'ur';
                                    }
                                @endphp

                                <article class="kes-blog-related-card">
                                    <a
                                        href="{{ route(
                                            'website.blog.show',
                                            $relatedParameters,
                                        ) }}"
                                    >
                                        @if (
                                            filled(
                                                $relatedPost
                                                    ->featured_image_path
                                            )
                                        )
                                            <img
                                                src="{{ asset(
                                                    'storage/'
                                                    .$relatedPost
                                                        ->featured_image_path,
                                                ) }}"
                                                alt="{{ $relatedPost
                                                    ->localized_image_alt_text }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <div
                                                class="kes-blog-related-placeholder"
                                            >
                                                ✦
                                            </div>
                                        @endif
                                    </a>

                                    <div>
                                        @if (
                                            filled(
                                                $relatedPost
                                                    ->localized_category
                                            )
                                        )
                                            <small>
                                                {{ $relatedPost
                                                    ->localized_category }}
                                            </small>
                                        @endif

                                        <h3>
                                            <a
                                                href="{{ route(
                                                    'website.blog.show',
                                                    $relatedParameters,
                                                ) }}"
                                            >
                                                {{ $relatedPost
                                                    ->localized_title }}
                                            </a>
                                        </h3>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </section>
    </main>
@endsection