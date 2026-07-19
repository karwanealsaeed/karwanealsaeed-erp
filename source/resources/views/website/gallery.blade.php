@extends('website.layout')

@section('content')
    <main class="kes-gallery-page">
        <section class="kes-gallery-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-gallery-hero-inner">
                <div>
                    <nav
                        class="kes-page-v2-breadcrumb"
                        aria-label="Breadcrumb"
                    >
                        <a href="{{ route('website.home') }}">
                            {{ app()->isLocale('ur') ? 'ہوم' : 'Home' }}
                        </a>

                        <span>/</span>

                        <span>{{ __('gallery.title') }}</span>
                    </nav>

                    <span class="kes-v2-section-label">
                        {{ __('gallery.eyebrow') }}
                    </span>

                    <h1>{{ __('gallery.title') }}</h1>

                    <p>{{ __('gallery.subtitle') }}</p>
                </div>

                <div class="kes-gallery-hero-logo">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Karwan E Al Saeed Travel & Tours"
                    >
                </div>
            </div>
        </section>

        <section class="kes-gallery-content">
            <div class="kes-v2-container">
                <form
                    method="GET"
                    action="{{ route('website.gallery') }}"
                    class="kes-gallery-filters"
                >
                    @if (app()->isLocale('ur'))
                        <input
                            type="hidden"
                            name="lang"
                            value="ur"
                        >
                    @endif

                    <div class="kes-gallery-filter-field">
                        <label for="gallery-category">
                            {{ app()->isLocale('ur')
                                ? 'کیٹیگری'
                                : 'Category' }}
                        </label>

                        <select
                            id="gallery-category"
                            name="category"
                        >
                            <option value="">
                                {{ __('gallery.all_categories') }}
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

                    <div class="kes-gallery-filter-field">
                        <label for="gallery-type">
                            {{ app()->isLocale('ur')
                                ? 'میڈیا کی قسم'
                                : 'Media Type' }}
                        </label>

                        <select
                            id="gallery-type"
                            name="type"
                        >
                            <option value="">
                                {{ __('gallery.all_media') }}
                            </option>

                            <option
                                value="Image"
                                @selected(
                                    $selectedMediaType === 'Image'
                                )
                            >
                                {{ __('gallery.images') }}
                            </option>

                            <option
                                value="Video"
                                @selected(
                                    $selectedMediaType === 'Video'
                                )
                            >
                                {{ __('gallery.videos') }}
                            </option>
                        </select>
                    </div>

                    <div class="kes-gallery-filter-actions">
                        <button
                            type="submit"
                            class="kes-v2-btn kes-v2-btn-gold"
                        >
                            {{ __('gallery.filter') }}
                        </button>

                        <a
                            href="{{ route(
                                'website.gallery',
                                app()->isLocale('ur')
                                    ? ['lang' => 'ur']
                                    : [],
                            ) }}"
                            class="kes-gallery-clear"
                        >
                            {{ __('gallery.clear_filters') }}
                        </a>
                    </div>
                </form>

                <div class="kes-gallery-results-heading">
                    <div>
                        <span class="kes-v2-section-label">
                            {{ __('gallery.eyebrow') }}
                        </span>

                        <h2>{{ __('gallery.title') }}</h2>
                    </div>

                    <strong>{{ $galleryItems->total() }}</strong>
                </div>

                <div class="kes-gallery-grid">
                    @forelse ($galleryItems as $galleryItem)
                        @php
                            $galleryRouteParameters = [
                                'galleryItem' => $galleryItem,
                            ];

                            if (app()->isLocale('ur')) {
                                $galleryRouteParameters['lang'] = 'ur';
                            }

                            $galleryUrl = route(
                                'website.gallery.show',
                                $galleryRouteParameters,
                            );

                            $previewPath =
                                $galleryItem->display_image;

                            $previewUrl = filled($previewPath)
                                ? asset('storage/'.$previewPath)
                                : null;
                        @endphp

                        <article class="kes-gallery-card">
                            <a
                                href="{{ $galleryUrl }}"
                                class="kes-gallery-card-media"
                                aria-label="{{ $galleryItem->localized_title }}"
                            >
                                @if ($previewUrl)
                                    <img
                                        src="{{ $previewUrl }}"
                                        alt="{{ $galleryItem->localized_alt_text }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="kes-gallery-placeholder">
                                        <span>
                                            {{ $galleryItem->is_video
                                                ? '▶'
                                                : '✦' }}
                                        </span>
                                    </div>
                                @endif

                                <div class="kes-gallery-card-overlay"></div>

                                <span class="kes-gallery-type-badge">
                                    {{ $galleryItem->is_video
                                        ? __('gallery.video')
                                        : __('gallery.image') }}
                                </span>

                                @if ($galleryItem->is_featured)
                                    <span class="kes-gallery-featured-badge">
                                        {{ __('gallery.featured') }}
                                    </span>
                                @endif

                                @if ($galleryItem->is_video)
                                    <span class="kes-gallery-play-button">
                                        ▶
                                    </span>
                                @endif
                            </a>

                            <div class="kes-gallery-card-body">
                                @if (
                                    filled(
                                        $galleryItem->localized_category
                                    )
                                )
                                    <span class="kes-gallery-card-category">
                                        {{ $galleryItem->localized_category }}
                                    </span>
                                @endif

                                <h2>
                                    <a href="{{ $galleryUrl }}">
                                        {{ $galleryItem->localized_title }}
                                    </a>
                                </h2>

                                @if (
                                    filled(
                                        $galleryItem->localized_caption
                                    )
                                )
                                    <p>
                                        {{ \Illuminate\Support\Str::limit(
                                            $galleryItem->localized_caption,
                                            110,
                                        ) }}
                                    </p>
                                @endif

                                <div class="kes-gallery-card-meta">
                                    @if (
                                        filled(
                                            $galleryItem->localized_location
                                        )
                                    )
                                        <span>
                                            {{ $galleryItem->localized_location }}
                                        </span>
                                    @endif

                                    @if ($galleryItem->event_date)
                                        <time
                                            datetime="{{ $galleryItem->event_date->toDateString() }}"
                                        >
                                            {{ $galleryItem->event_date->format(
                                                'd M Y',
                                            ) }}
                                        </time>
                                    @endif
                                </div>

                                <a
                                    href="{{ $galleryUrl }}"
                                    class="kes-gallery-card-link"
                                >
                                    {{ $galleryItem->is_video
                                        ? __('gallery.watch_video')
                                        : __('gallery.view_details') }}

                                    <span>
                                        {{ app()->isLocale('ur')
                                            ? '←'
                                            : '→' }}
                                    </span>
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="kes-gallery-empty">
                            <div class="kes-gallery-empty-icon">
                                ✦
                            </div>

                            <h2>
                                {{ __('gallery.no_items_title') }}
                            </h2>

                            <p>
                                {{ __('gallery.no_items_text') }}
                            </p>

                            <a
                                href="{{ route(
                                    'website.gallery',
                                    app()->isLocale('ur')
                                        ? ['lang' => 'ur']
                                        : [],
                                ) }}"
                                class="kes-v2-btn kes-v2-btn-gold"
                            >
                                {{ __('gallery.clear_filters') }}
                            </a>
                        </div>
                    @endforelse
                </div>

                @if ($galleryItems->hasPages())
                    <div class="kes-gallery-pagination">
                        {{ $galleryItems->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>
@endsection