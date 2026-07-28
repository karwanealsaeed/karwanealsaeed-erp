@extends('website.inner-v3-layout')

@section('content')
    @php
        $listingParameters = app()->isLocale('ur')
            ? ['lang' => 'ur']
            : [];

        $coverImage = $tour->cover_image
    ? asset('storage/'.$tour->cover_image)
    : null;

        $priceNote = app()->isLocale('ur')
            && filled($tour->price_note_ur)
                ? $tour->price_note_ur
                : $tour->price_note;

        $galleryImages = collect($tour->gallery_images ?? [])
            ->filter()
            ->values();

        $whatsappNumber = preg_replace(
            '/[^0-9]/',
            '',
            $company->whatsapp
                ?? $company->mobile
                ?? '+923012592342',
        );

        $whatsappMessage = urlencode(
            'Assalam-o-Alaikum, mujhe Pakistan Tour "'
            .$tour->localized_title
            .'" ke bare mein details chahiye.',
        );
    @endphp

    <main class="kes-tour-detail-page">
        <section class="kes-tour-detail-hero">
            @if ($coverImage)
    <img
        src="{{ $coverImage }}"
        alt="{{ $tour->localized_title }}"
        class="kes-tour-detail-hero-image"
    >
@endif

            <div class="kes-tour-detail-overlay"></div>

            <div class="kes-v2-container kes-tour-detail-hero-content">
                <nav
                    class="kes-page-v2-breadcrumb"
                    aria-label="Breadcrumb"
                >
                    <a href="{{ route('website.home') }}">
                        {{ __('tours.home') }}
                    </a>

                    <span>/</span>

                    <a
                        href="{{ route(
                            'website.tours',
                            $listingParameters,
                        ) }}"
                    >
                        {{ __('tours.pakistan_tours') }}
                    </a>

                    <span>/</span>

                    <span>{{ $tour->localized_title }}</span>
                </nav>

                <div class="kes-tour-detail-heading">
                    <div>
                        @if ($tour->is_featured)
                            <span class="kes-tour-featured-badge">
                                {{ __('tours.featured') }}
                            </span>
                        @endif

                        <span class="kes-v2-section-label">
                            {{ $tour->tour_no }}
                        </span>

                        <h1>{{ $tour->localized_title }}</h1>

                        @if (filled($tour->localized_subtitle))
                            <p>{{ $tour->localized_subtitle }}</p>
                        @endif

                        <div class="kes-tour-detail-location">
                            <strong>{{ $tour->destination }}</strong>

                            @if (filled($tour->province))
                                <span>•</span>
                                <span>{{ $tour->province }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="kes-tour-detail-price">
                        <span>{{ __('tours.starting_from') }}</span>

                        @if ((float) $tour->price > 0)
                            <strong>{{ $tour->formatted_price }}</strong>

                            <small>
                                {{ $priceNote ?: __('tours.per_person') }}
                            </small>
                        @else
                            <strong>
                                {{ __('tours.price_on_request') }}
                            </strong>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="kes-tour-detail-content">
            <div class="kes-v2-container kes-tour-detail-grid">
                <article class="kes-tour-detail-main">
                    <section class="kes-tour-detail-summary">
                        <h2>{{ __('tours.tour_information') }}</h2>

                        <div class="kes-tour-detail-facts">
                            <div>
                                <span>{{ __('tours.destination') }}</span>
                                <strong>{{ $tour->destination }}</strong>
                            </div>

                            @if (filled($tour->province))
                                <div>
                                    <span>{{ __('tours.province') }}</span>
                                    <strong>{{ $tour->province }}</strong>
                                </div>
                            @endif

                            @if (filled($tour->departure_city))
                                <div>
                                    <span>
                                        {{ __('tours.departure_city') }}
                                    </span>

                                    <strong>
                                        {{ $tour->departure_city }}
                                    </strong>
                                </div>
                            @endif

                            @if ($tour->start_date)
                                <div>
                                    <span>
                                        {{ __('tours.departure_date') }}
                                    </span>

                                    <strong>
                                        {{ $tour->start_date->format('d M Y') }}
                                    </strong>
                                </div>
                            @endif

                            @if ($tour->end_date)
                                <div>
                                    <span>
                                        {{ __('tours.return_date') }}
                                    </span>

                                    <strong>
                                        {{ $tour->end_date->format('d M Y') }}
                                    </strong>
                                </div>
                            @endif

                            @if (filled($tour->duration_days))
                                <div>
                                    <span>{{ __('tours.duration') }}</span>

                                    <strong>
                                        {{ $tour->duration_days }}
                                        {{ __('tours.days') }}

                                        @if (filled($tour->duration_nights))
                                            /
                                            {{ $tour->duration_nights }}
                                            {{ __('tours.nights') }}
                                        @endif
                                    </strong>
                                </div>
                            @endif

                            @if (filled($tour->group_size))
                                <div>
                                    <span>{{ __('tours.group_size') }}</span>
                                    <strong>{{ $tour->group_size }}</strong>
                                </div>
                            @endif

                            @if (filled($tour->seats_available))
                                <div>
                                    <span>
                                        {{ __('tours.seats_available') }}
                                    </span>

                                    <strong>
                                        {{ number_format(
                                            $tour->seats_available,
                                        ) }}
                                    </strong>
                                </div>
                            @endif

                            @if (filled($tour->seats_left))
                                <div>
                                    <span>{{ __('tours.seats_left') }}</span>

                                    <strong>
                                        {{ number_format($tour->seats_left) }}
                                    </strong>
                                </div>
                            @endif
                        </div>
                    </section>

                    @if (
                        filled($tour->localized_short_description)
                        || filled($tour->localized_description)
                    )
                        <section class="kes-tour-detail-section">
                            <span class="kes-v2-section-label">
                                {{ __('tours.tour_overview') }}
                            </span>

                            <h2>{{ __('tours.tour_overview') }}</h2>

                            @if (filled($tour->localized_short_description))
                                <p class="kes-tour-detail-intro">
                                    {{ $tour->localized_short_description }}
                                </p>
                            @endif

                            @if (filled($tour->localized_description))
                                <div class="kes-tour-detail-description">
                                    {!! nl2br(
                                        e($tour->localized_description),
                                    ) !!}
                                </div>
                            @endif
                        </section>
                    @endif

                    @if (count($tour->localized_highlights) > 0)
                        <section class="kes-tour-detail-section">
                            <span class="kes-v2-section-label">
                                {{ __('tours.tour_highlights') }}
                            </span>

                            <h2>{{ __('tours.tour_highlights') }}</h2>

                            <ul class="kes-tour-detail-list">
                                @foreach (
                                    $tour->localized_highlights
                                    as $highlight
                                )
                                    <li>{{ $highlight }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if (
                        count($tour->localized_inclusions) > 0
                        || count($tour->localized_exclusions) > 0
                    )
                        <section class="kes-tour-detail-inclusions">
                            @if (count($tour->localized_inclusions) > 0)
                                <div class="kes-tour-detail-included">
                                    <h2>
                                        {{ __('tours.included_services') }}
                                    </h2>

                                    <ul>
                                        @foreach (
                                            $tour->localized_inclusions
                                            as $inclusion
                                        )
                                            <li>{{ $inclusion }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (count($tour->localized_exclusions) > 0)
                                <div class="kes-tour-detail-excluded">
                                    <h2>{{ __('tours.not_included') }}</h2>

                                    <ul>
                                        @foreach (
                                            $tour->localized_exclusions
                                            as $exclusion
                                        )
                                            <li>{{ $exclusion }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </section>
                    @endif

                    @if ($galleryImages->isNotEmpty())
                        <section class="kes-tour-detail-section">
                            <span class="kes-v2-section-label">
                                {{ __('tours.tour_gallery') }}
                            </span>

                            <h2>{{ __('tours.tour_gallery') }}</h2>

                            <div class="kes-tour-detail-gallery">
                                @foreach ($galleryImages as $galleryImage)
                                    <a
                                        href="{{ asset(
                                            'storage/'.$galleryImage,
                                        ) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <img
                                            src="{{ asset(
                                                'storage/'.$galleryImage,
                                            ) }}"
                                            alt="{{ $tour->localized_title }}"
                                            loading="lazy"
                                        >
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </article>

                <aside class="kes-tour-detail-sidebar">
                    <div class="kes-tour-booking-card">
                        <span class="kes-v2-section-label">
                            {{ __('tours.book_now') }}
                        </span>

                        <h2>{{ $tour->localized_title }}</h2>

                        <div class="kes-tour-booking-price">
                            @if ((float) $tour->price > 0)
                                <span>
                                    {{ __('tours.starting_from') }}
                                </span>

                                <strong>
                                    {{ $tour->formatted_price }}
                                </strong>

                                <small>
                                    {{ $priceNote
                                        ?: __('tours.per_person') }}
                                </small>
                            @else
                                <strong>
                                    {{ __('tours.price_on_request') }}
                                </strong>
                            @endif
                        </div>

                        <p>{{ __('tours.support_text') }}</p>

                        <a
                            href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                            target="_blank"
                            rel="noopener"
                            class="kes-v2-btn kes-v2-btn-gold"
                        >
                            {{ __('tours.whatsapp') }}
                        </a>

                        <a
                            href="{{ route('website.contact') }}"
                            class="kes-v2-btn kes-tour-contact-button"
                        >
                            {{ __('tours.send_inquiry') }}
                        </a>
                    </div>

                    <div class="kes-tour-help-card">
                        <h3>{{ __('tours.need_help') }}</h3>

                        <p>{{ __('tours.support_text') }}</p>

                        <a
                            href="{{ route(
                                'website.tours',
                                $listingParameters,
                            ) }}"
                        >
                            {{ __('tours.back_to_tours') }}

                            <span>
                                {{ app()->isLocale('ur') ? '←' : '→' }}
                            </span>
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    </main>
@endsection