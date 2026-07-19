@extends('website.layout')

@section('content')
    @php
        $listingParameters = app()->isLocale('ur')
            ? ['lang' => 'ur']
            : [];

        $previewPath = $galleryItem->display_image;

        $previewUrl = filled($previewPath)
            ? asset('storage/'.$previewPath)
            : null;
    @endphp

    <main class="kes-gallery-detail-page">
        <section class="kes-gallery-detail-hero">
            @if ($previewUrl)
                <img
                    src="{{ $previewUrl }}"
                    alt="{{ $galleryItem->localized_alt_text }}"
                    class="kes-gallery-detail-hero-image"
                >
            @endif

            <div class="kes-gallery-detail-overlay"></div>

            <div class="kes-v2-container kes-gallery-detail-hero-content">
                <nav
                    class="kes-page-v2-breadcrumb"
                    aria-label="Breadcrumb"
                >
                    <a href="{{ route('website.home') }}">
                        {{ app()->isLocale('ur') ? 'ہوم' : 'Home' }}
                    </a>

                    <span>/</span>

                    <a
                        href="{{ route(
                            'website.gallery',
                            $listingParameters,
                        ) }}"
                    >
                        {{ __('gallery.title') }}
                    </a>

                    <span>/</span>

                    <span>{{ $galleryItem->localized_title }}</span>
                </nav>

                <div class="kes-gallery-detail-heading">
                    <div>
                        <div class="kes-gallery-detail-badges">
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
                        </div>

                        @if (
                            filled(
                                $galleryItem->localized_category
                            )
                        )
                            <span class="kes-v2-section-label">
                                {{ $galleryItem->localized_category }}
                            </span>
                        @endif

                        <h1>{{ $galleryItem->localized_title }}</h1>

                        @if (
                            filled(
                                $galleryItem->localized_caption
                            )
                        )
                            <p>{{ $galleryItem->localized_caption }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="kes-gallery-detail-content">
            <div class="kes-v2-container kes-gallery-detail-grid">
                <article class="kes-gallery-detail-main">
                    <div class="kes-gallery-detail-media">
                        @if ($previewUrl)
                            <img
                                src="{{ $previewUrl }}"
                                alt="{{ $galleryItem->localized_alt_text }}"
                            >
                        @else
                            <div class="kes-gallery-detail-placeholder">
                                <span>
                                    {{ $galleryItem->is_video
                                        ? '▶'
                                        : '✦' }}
                                </span>
                            </div>
                        @endif

                        @if (
                            $galleryItem->is_video
                            && filled($galleryItem->video_url)
                        )
                            <a
                                href="{{ $galleryItem->video_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="kes-gallery-detail-play"
                            >
                                <span>▶</span>

                                {{ __('gallery.open_video') }}
                            </a>
                        @endif
                    </div>

                    @if (
                        filled(
                            $galleryItem->localized_caption
                        )
                    )
                        <section class="kes-gallery-detail-section">
                            <span class="kes-v2-section-label">
                                {{ __('gallery.about_this_item') }}
                            </span>

                            <h2>
                                {{ __('gallery.about_this_item') }}
                            </h2>

                            <div class="kes-gallery-detail-description">
                                {!! nl2br(
                                    e(
                                        $galleryItem
                                            ->localized_caption,
                                    ),
                                ) !!}
                            </div>
                        </section>
                    @endif
                </article>

                <aside class="kes-gallery-detail-sidebar">
                    <div class="kes-gallery-info-card">
                        <span class="kes-v2-section-label">
                            {{ __('gallery.about_this_item') }}
                        </span>

                        <h2>{{ $galleryItem->localized_title }}</h2>

                        <dl class="kes-gallery-detail-facts">
                            @if (
                                filled(
                                    $galleryItem
                                        ->localized_category
                                )
                            )
                                <div>
                                    <dt>
                                        {{ app()->isLocale('ur')
                                            ? 'کیٹیگری'
                                            : 'Category' }}
                                    </dt>

                                    <dd>
                                        {{ $galleryItem
                                            ->localized_category }}
                                    </dd>
                                </div>
                            @endif

                            @if (
                                filled(
                                    $galleryItem
                                        ->localized_album
                                )
                            )
                                <div>
                                    <dt>{{ __('gallery.album') }}</dt>

                                    <dd>
                                        {{ $galleryItem
                                            ->localized_album }}
                                    </dd>
                                </div>
                            @endif

                            @if (
                                filled(
                                    $galleryItem
                                        ->localized_location
                                )
                            )
                                <div>
                                    <dt>{{ __('gallery.location') }}</dt>

                                    <dd>
                                        {{ $galleryItem
                                            ->localized_location }}
                                    </dd>
                                </div>
                            @endif

                            @if ($galleryItem->event_date)
                                <div>
                                    <dt>
                                        {{ __('gallery.event_date') }}
                                    </dt>

                                    <dd>
                                        {{ $galleryItem
                                            ->event_date
                                            ->format('d M Y') }}
                                    </dd>
                                </div>
                            @endif

                            @if (
                                filled(
                                    $galleryItem->photographer
                                )
                            )
                                <div>
                                    <dt>
                                        {{ __('gallery.photographer') }}
                                    </dt>

                                    <dd>
                                        {{ $galleryItem
                                            ->photographer }}
                                    </dd>
                                </div>
                            @endif
                        </dl>

                        @if (
                            $galleryItem->is_video
                            && filled($galleryItem->video_url)
                        )
                            <a
                                href="{{ $galleryItem->video_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="kes-v2-btn kes-v2-btn-gold"
                            >
                                {{ __('gallery.open_video') }}
                            </a>
                        @endif
                    </div>

                    <div class="kes-gallery-back-card">
                        <h3>{{ __('gallery.title') }}</h3>

                        <p>{{ __('gallery.subtitle') }}</p>

                        <a
                            href="{{ route(
                                'website.gallery',
                                $listingParameters,
                            ) }}"
                        >
                            {{ __('gallery.back_to_gallery') }}

                            <span>
                                {{ app()->isLocale('ur')
                                    ? '←'
                                    : '→' }}
                            </span>
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    </main>
@endsection