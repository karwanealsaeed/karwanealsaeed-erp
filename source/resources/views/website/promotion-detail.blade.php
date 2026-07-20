@extends('website.layout')

@section('content')
    @php
        $promotionsRouteParameters = [];

        if (app()->isLocale('ur')) {
            $promotionsRouteParameters['lang'] = 'ur';
        }

        $currency = $promotion->currency ?: 'PKR';

        $contactParameters = [];

        if (app()->isLocale('ur')) {
            $contactParameters['lang'] = 'ur';
        }

        if (filled($promotion->target_url)) {
            $ctaUrl = \Illuminate\Support\Str::startsWith(
                $promotion->target_url,
                ['http://', 'https://'],
            )
                ? $promotion->target_url
                : url($promotion->target_url);
        } else {
            $ctaUrl = route(
                'website.contact',
                $contactParameters,
            );
        }

        $discountText = null;

        if (
            filled($promotion->discount_value)
            && $promotion->discount_type === 'Percentage'
        ) {
            $discountText = __(
                'promotions.percentage_off',
                [
                    'value' => rtrim(
                        rtrim(
                            number_format(
                                (float) $promotion->discount_value,
                                2,
                                '.',
                                '',
                            ),
                            '0',
                        ),
                        '.',
                    ),
                ],
            );
        } elseif (
            filled($promotion->discount_value)
            && $promotion->discount_type === 'Fixed Amount'
        ) {
            $discountText = __(
                'promotions.amount_off',
                [
                    'currency' => $currency,
                    'value' => number_format(
                        (float) $promotion->discount_value,
                        0,
                    ),
                ],
            );
        }
    @endphp

    <main class="kes-promotion-detail-page">
        <section class="kes-promotion-detail-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-promotion-detail-hero-inner">
                <div class="kes-promotion-detail-hero-copy">
                    <nav
                        class="kes-page-v2-breadcrumb"
                        aria-label="Breadcrumb"
                    >
                        <a href="{{ route('website.home') }}">
                            {{ __('promotions.home') }}
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route(
                                'website.promotions',
                                $promotionsRouteParameters,
                            ) }}"
                        >
                            {{ __('promotions.title') }}
                        </a>

                        <span>/</span>

                        <span>
                            {{ $promotion->localized_title }}
                        </span>
                    </nav>

                    @if (filled($promotion->promotion_type))
                        <span class="kes-v2-section-label">
                            {{ $promotion->promotion_type }}
                        </span>
                    @endif

                    <h1>{{ $promotion->localized_title }}</h1>

                    @if (
                        filled($promotion->localized_subtitle)
                    )
                        <p>
                            {{ $promotion->localized_subtitle }}
                        </p>
                    @endif

                    <div class="kes-promotion-detail-badges">
                        @if ($promotion->is_featured)
                            <span>
                                {{ __('promotions.featured') }}
                            </span>
                        @endif

                        @if (
                            filled(
                                $promotion->localized_badge_text
                            )
                        )
                            <span>
                                {{ $promotion
                                    ->localized_badge_text }}
                            </span>
                        @endif

                        @if (filled($discountText))
                            <strong>{{ $discountText }}</strong>
                        @endif
                    </div>
                </div>

                @if (
                    filled($promotion->featured_image_path)
                )
                    <figure class="kes-promotion-detail-hero-image">
                        <img
                            src="{{ asset(
                                'storage/'
                                .$promotion->featured_image_path,
                            ) }}"
                            alt="{{ $promotion->localized_title }}"
                        >
                    </figure>
                @else
                    <div
                        class="kes-promotion-detail-hero-placeholder"
                        aria-hidden="true"
                    >
                        <span>%</span>
                        <small>
                            {{ __('promotions.limited_offer') }}
                        </small>
                    </div>
                @endif
            </div>
        </section>

        <section class="kes-promotion-detail-content">
            <div class="kes-v2-container">
                <div class="kes-promotion-detail-layout">
                    <article class="kes-promotion-detail-main">
                        <section class="kes-promotion-information-card">
                            <span class="kes-v2-section-label">
                                {{ __('promotions.offer_details') }}
                            </span>

                            <h2>
                                {{ $promotion->localized_title }}
                            </h2>

                            @if (
                                filled(
                                    $promotion->localized_description
                                )
                            )
                                <div class="kes-promotion-description">
                                    {!! nl2br(
                                        e(
                                            $promotion
                                                ->localized_description,
                                        ),
                                    ) !!}
                                </div>
                            @endif
                        </section>

                        @if (
                            filled($promotion->localized_terms)
                        )
                            <section class="kes-promotion-terms-card">
                                <h2>
                                    {{ __('promotions.terms_conditions') }}
                                </h2>

                                <div>
                                    {!! nl2br(
                                        e(
                                            $promotion
                                                ->localized_terms,
                                        ),
                                    ) !!}
                                </div>
                            </section>
                        @endif
                    </article>

                    <aside class="kes-promotion-booking-card">
                        @if (
                            filled($promotion->original_price)
                            || filled($promotion->offer_price)
                        )
                            <div class="kes-promotion-detail-pricing">
                                @if (
                                    filled(
                                        $promotion->original_price
                                    )
                                )
                                    <span>
                                        <small>
                                            {{ __('promotions.original_price') }}
                                        </small>

                                        <del>
                                            {{ $currency }}
                                            {{ number_format(
                                                (float) $promotion
                                                    ->original_price,
                                                0,
                                            ) }}
                                        </del>
                                    </span>
                                @endif

                                @if (
                                    filled(
                                        $promotion->offer_price
                                    )
                                )
                                    <span>
                                        <small>
                                            {{ __('promotions.offer_price') }}
                                        </small>

                                        <strong>
                                            {{ $currency }}
                                            {{ number_format(
                                                (float) $promotion
                                                    ->offer_price,
                                                0,
                                            ) }}
                                        </strong>
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if (filled($promotion->promo_code))
                            <div class="kes-promotion-detail-code">
                                <small>
                                    {{ __('promotions.promo_code') }}
                                </small>

                                <strong>
                                    {{ $promotion->promo_code }}
                                </strong>

                                <p>
                                    {{ __('promotions.copy_code') }}
                                </p>
                            </div>
                        @endif

                        <div class="kes-promotion-validity">
                            <h3>
                                {{ __('promotions.validity') }}
                            </h3>

                            @if ($promotion->starts_at)
                                <p>
                                    <span>
                                        {{ __('promotions.starts_on') }}
                                    </span>

                                    <strong>
                                        {{ $promotion
                                            ->starts_at
                                            ->format('d M Y') }}
                                    </strong>
                                </p>
                            @endif

                            @if ($promotion->ends_at)
                                <p>
                                    <span>
                                        {{ __('promotions.ends_on') }}
                                    </span>

                                    <strong>
                                        {{ $promotion
                                            ->ends_at
                                            ->format('d M Y') }}
                                    </strong>
                                </p>
                            @else
                                <p>
                                    <span>
                                        {{ __('promotions.available_now') }}
                                    </span>

                                    <strong>
                                        {{ __('promotions.no_expiry') }}
                                    </strong>
                                </p>
                            @endif
                        </div>

                        <p class="kes-promotion-contact-note">
                            {{ __('promotions.contact_team') }}
                        </p>

                        <a
                            href="{{ $ctaUrl }}"
                            class="kes-v2-btn kes-v2-btn-gold"
                            @if (
                                \Illuminate\Support\Str::startsWith(
                                    $ctaUrl,
                                    ['http://', 'https://'],
                                )
                                && ! \Illuminate\Support\Str::startsWith(
                                    $ctaUrl,
                                    url('/'),
                                )
                            )
                                target="_blank"
                                rel="noopener noreferrer"
                            @endif
                        >
                            {{ $promotion->localized_cta_text }}
                        </a>

                        <a
                            href="{{ route(
                                'website.promotions',
                                $promotionsRouteParameters,
                            ) }}"
                            class="kes-promotion-back-link"
                        >
                            {{ __('promotions.back_to_promotions') }}
                        </a>
                    </aside>
                </div>

                @if ($relatedPromotions->isNotEmpty())
                    <section class="kes-promotion-related">
                        <div class="kes-promotion-related-heading">
                            <span class="kes-v2-section-label">
                                {{ __('promotions.eyebrow') }}
                            </span>

                            <h2>
                                {{ __('promotions.related_offers') }}
                            </h2>
                        </div>

                        <div class="kes-promotion-related-grid">
                            @foreach (
                                $relatedPromotions as $relatedPromotion
                            )
                                @php
                                    $relatedParameters = [
                                        'promotion' =>
                                            $relatedPromotion,
                                    ];

                                    if (app()->isLocale('ur')) {
                                        $relatedParameters['lang'] =
                                            'ur';
                                    }
                                @endphp

                                <article class="kes-promotion-related-card">
                                    <a
                                        href="{{ route(
                                            'website.promotions.show',
                                            $relatedParameters,
                                        ) }}"
                                    >
                                        @if (
                                            filled(
                                                $relatedPromotion
                                                    ->featured_image_path
                                            )
                                        )
                                            <img
                                                src="{{ asset(
                                                    'storage/'
                                                    .$relatedPromotion
                                                        ->featured_image_path,
                                                ) }}"
                                                alt="{{ $relatedPromotion
                                                    ->localized_title }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <div
                                                class="kes-promotion-related-placeholder"
                                            >
                                                %
                                            </div>
                                        @endif
                                    </a>

                                    <div>
                                        @if (
                                            filled(
                                                $relatedPromotion
                                                    ->promotion_type
                                            )
                                        )
                                            <small>
                                                {{ $relatedPromotion
                                                    ->promotion_type }}
                                            </small>
                                        @endif

                                        <h3>
                                            <a
                                                href="{{ route(
                                                    'website.promotions.show',
                                                    $relatedParameters,
                                                ) }}"
                                            >
                                                {{ $relatedPromotion
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