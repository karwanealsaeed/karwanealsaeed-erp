@extends('website.layout')

@section('content')
    <main class="kes-promotions-page">
        <section class="kes-promotions-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-promotions-hero-inner">
                <div class="kes-promotions-hero-copy">
                    <nav
                        class="kes-page-v2-breadcrumb"
                        aria-label="Breadcrumb"
                    >
                        <a href="{{ route('website.home') }}">
                            {{ __('promotions.home') }}
                        </a>

                        <span>/</span>

                        <span>{{ __('promotions.title') }}</span>
                    </nav>

                    <span class="kes-v2-section-label">
                        {{ __('promotions.eyebrow') }}
                    </span>

                    <h1>{{ __('promotions.title') }}</h1>

                    <p>{{ __('promotions.subtitle') }}</p>
                </div>

                <div
                    class="kes-promotions-hero-art"
                    aria-hidden="true"
                >
                    <span class="kes-promotions-art-ticket"></span>
                    <span class="kes-promotions-art-percent">%</span>
                    <span class="kes-promotions-art-spark">&#10022;</span>
                </div>
            </div>
        </section>

        <section class="kes-promotions-content">
            <div class="kes-v2-container">
                <form
                    method="GET"
                    action="{{ route('website.promotions') }}"
                    class="kes-promotions-filters"
                >
                    @if (app()->isLocale('ur'))
                        <input
                            type="hidden"
                            name="lang"
                            value="ur"
                        >
                    @endif

                    <div class="kes-promotions-filter-field">
                        <label for="promotion-search">
                            {{ __('promotions.search') }}
                        </label>

                        <input
                            id="promotion-search"
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="{{ __('promotions.search_placeholder') }}"
                        >
                    </div>

                    <div class="kes-promotions-filter-field">
                        <label for="promotion-type">
                            {{ __('promotions.promotion_type') }}
                        </label>

                        <select
                            id="promotion-type"
                            name="type"
                        >
                            <option value="">
                                {{ __('promotions.all_types') }}
                            </option>

                            @foreach ($promotionTypes as $promotionType)
                                <option
                                    value="{{ $promotionType }}"
                                    @selected(
                                        $selectedType === $promotionType
                                    )
                                >
                                    {{ $promotionType }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="kes-promotions-filter-actions">
                        <button
                            type="submit"
                            class="kes-v2-btn kes-v2-btn-gold"
                        >
                            {{ __('promotions.filter') }}
                        </button>

                        <a
                            href="{{ route(
                                'website.promotions',
                                app()->isLocale('ur')
                                    ? ['lang' => 'ur']
                                    : [],
                            ) }}"
                            class="kes-promotions-clear"
                        >
                            {{ __('promotions.clear_filters') }}
                        </a>
                    </div>
                </form>

                <div class="kes-promotions-results-heading">
                    <div>
                        <span class="kes-v2-section-label">
                            {{ __('promotions.eyebrow') }}
                        </span>

                        <h2>{{ __('promotions.results_title') }}</h2>
                    </div>

                    <strong>
                        {{ trans_choice(
                            'promotions.offers_count',
                            $promotions->total(),
                            ['count' => $promotions->total()],
                        ) }}
                    </strong>
                </div>

                <div class="kes-promotions-grid">
                    @forelse ($promotions as $promotion)
                        @php
                            $routeParameters = [
                                'promotion' => $promotion,
                            ];

                            if (app()->isLocale('ur')) {
                                $routeParameters['lang'] = 'ur';
                            }

                            $promotionUrl = route(
                                'website.promotions.show',
                                $routeParameters,
                            );

                            $description = $promotion
                                ->localized_description
                                ?: $promotion->localized_subtitle;

                            $currency = $promotion->currency ?: 'PKR';

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
                                                    (float) $promotion
                                                        ->discount_value,
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
                                            (float) $promotion
                                                ->discount_value,
                                            0,
                                        ),
                                    ],
                                );
                            }
                        @endphp

                        <article
                            class="kes-promotion-card {{ $promotion->is_featured
                                ? 'is-featured'
                                : '' }}"
                        >
                            <a
                                href="{{ $promotionUrl }}"
                                class="kes-promotion-card-media"
                                aria-label="{{ $promotion->localized_title }}"
                            >
                                @if (filled($promotion->featured_image_path))
                                    <img
                                        src="{{ asset(
                                            'storage/'
                                            .$promotion->featured_image_path,
                                        ) }}"
                                        alt="{{ $promotion->localized_title }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="kes-promotion-placeholder">
                                        <span>%</span>

                                        <small>
                                            {{ $promotion->promotion_type
                                                ?: __('promotions.eyebrow') }}
                                        </small>
                                    </div>
                                @endif

                                <div class="kes-promotion-media-overlay"></div>

                                @if ($promotion->is_featured)
                                    <span class="kes-promotion-featured">
                                        {{ __('promotions.featured') }}
                                    </span>
                                @endif

                                @if (
                                    filled(
                                        $promotion->localized_badge_text
                                    )
                                )
                                    <span class="kes-promotion-badge">
                                        {{ $promotion
                                            ->localized_badge_text }}
                                    </span>
                                @endif

                                @if (filled($discountText))
                                    <strong class="kes-promotion-discount">
                                        {{ $discountText }}
                                    </strong>
                                @endif
                            </a>

                            <div class="kes-promotion-card-body">
                                @if (filled($promotion->promotion_type))
                                    <span class="kes-promotion-type">
                                        {{ $promotion->promotion_type }}
                                    </span>
                                @endif

                                <h2>
                                    <a href="{{ $promotionUrl }}">
                                        {{ $promotion->localized_title }}
                                    </a>
                                </h2>

                                @if (
                                    filled(
                                        $promotion->localized_subtitle
                                    )
                                )
                                    <h3>
                                        {{ $promotion
                                            ->localized_subtitle }}
                                    </h3>
                                @endif

                                @if (filled($description))
                                    <p>
                                        {{ \Illuminate\Support\Str::limit(
                                            trim(strip_tags($description)),
                                            145,
                                        ) }}
                                    </p>
                                @endif

                                @if (
                                    filled($promotion->original_price)
                                    || filled($promotion->offer_price)
                                )
                                    <div class="kes-promotion-pricing">
                                        @if (
                                            filled(
                                                $promotion->original_price
                                            )
                                        )
                                            <span>
                                                {{ $currency }}
                                                {{ number_format(
                                                    (float) $promotion
                                                        ->original_price,
                                                    0,
                                                ) }}
                                            </span>
                                        @endif

                                        @if (
                                            filled(
                                                $promotion->offer_price
                                            )
                                        )
                                            <strong>
                                                {{ $currency }}
                                                {{ number_format(
                                                    (float) $promotion
                                                        ->offer_price,
                                                    0,
                                                ) }}
                                            </strong>
                                        @endif
                                    </div>
                                @endif

                                <div class="kes-promotion-card-footer">
                                    @if (filled($promotion->promo_code))
                                        <span class="kes-promotion-code">
                                            {{ __('promotions.promo_code') }}:
                                            <strong>
                                                {{ $promotion->promo_code }}
                                            </strong>
                                        </span>
                                    @elseif ($promotion->ends_at)
                                        <span>
                                            {{ __('promotions.ends_on') }}:
                                            {{ $promotion
                                                ->ends_at
                                                ->format('d M Y') }}
                                        </span>
                                    @else
                                        <span>
                                            {{ __('promotions.available_now') }}
                                        </span>
                                    @endif

                                    <a href="{{ $promotionUrl }}">
                                        {{ __('promotions.view_offer') }}

                                        <span aria-hidden="true">
                                            {{ app()->isLocale('ur')
                                                ? '←' : '→' }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="kes-promotions-empty">
                            <span aria-hidden="true">%</span>

                            <h2>
                                {{ __('promotions.no_items_title') }}
                            </h2>

                            <p>
                                {{ __('promotions.no_items_text') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($promotions->hasPages())
                    <div class="kes-promotions-pagination">
                        {{ $promotions->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>
@endsection