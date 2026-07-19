@extends('website.layout')

@section('content')
    <main class="kes-tours-page">
        <section class="kes-tours-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-tours-hero-inner">
                <div>
                    <nav
                        class="kes-page-v2-breadcrumb"
                        aria-label="Breadcrumb"
                    >
                        <a href="{{ route('website.home') }}">
                            {{ __('tours.home') }}
                        </a>

                        <span>/</span>

                        <span>
                            {{ __('tours.pakistan_tours') }}
                        </span>
                    </nav>

                    <span class="kes-v2-section-label">
                        {{ __('tours.page_label') }}
                    </span>

                    <h1>{{ __('tours.page_heading') }}</h1>

                    <p>{{ __('tours.page_description') }}</p>
                </div>

                <div class="kes-tours-hero-logo">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Karwan E Al Saeed Travel & Tours"
                    >
                </div>
            </div>
        </section>

        <section class="kes-tours-content">
            <div class="kes-v2-container">
                <form
                    method="GET"
                    action="{{ route('website.tours') }}"
                    class="kes-tours-filters"
                >
                    @if (app()->isLocale('ur'))
                        <input
                            type="hidden"
                            name="lang"
                            value="ur"
                        >
                    @endif

                    <div class="kes-tours-filter-field kes-tours-filter-search">
                        <label for="tour-search">
                            {{ __('tours.available_tours') }}
                        </label>

                        <input
                            id="tour-search"
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('tours.search_placeholder') }}"
                        >
                    </div>

                    <div class="kes-tours-filter-field">
                        <label for="tour-province">
                            {{ __('tours.province') }}
                        </label>

                        <select
                            id="tour-province"
                            name="province"
                        >
                            <option value="">
                                {{ __('tours.all_provinces') }}
                            </option>

                            @foreach ($provinces as $province)
                                <option
                                    value="{{ $province }}"
                                    @selected(request('province') === $province)
                                >
                                    {{ $province }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="kes-tours-filter-field">
                        <label for="tour-duration">
                            {{ __('tours.duration') }}
                        </label>

                        <select
                            id="tour-duration"
                            name="duration"
                        >
                            <option value="">
                                {{ __('tours.all_durations') }}
                            </option>

                            <option
                                value="1-3"
                                @selected(request('duration') === '1-3')
                            >
                                {{ __('tours.duration_1_3') }}
                            </option>

                            <option
                                value="4-7"
                                @selected(request('duration') === '4-7')
                            >
                                {{ __('tours.duration_4_7') }}
                            </option>

                            <option
                                value="8+"
                                @selected(request('duration') === '8+')
                            >
                                {{ __('tours.duration_8_plus') }}
                            </option>
                        </select>
                    </div>

                    <div class="kes-tours-filter-field">
                        <label for="tour-sort">
                            {{ __('tours.available_tours') }}
                        </label>

                        <select
                            id="tour-sort"
                            name="sort"
                        >
                            <option
                                value=""
                                @selected(blank(request('sort')))
                            >
                                {{ __('tours.sort_featured') }}
                            </option>

                            <option
                                value="latest"
                                @selected(request('sort') === 'latest')
                            >
                                {{ __('tours.sort_latest') }}
                            </option>

                            <option
                                value="price_low"
                                @selected(request('sort') === 'price_low')
                            >
                                {{ __('tours.sort_price_low') }}
                            </option>

                            <option
                                value="price_high"
                                @selected(request('sort') === 'price_high')
                            >
                                {{ __('tours.sort_price_high') }}
                            </option>

                            <option
                                value="duration_low"
                                @selected(request('sort') === 'duration_low')
                            >
                                {{ __('tours.sort_duration_low') }}
                            </option>
                        </select>
                    </div>

                    <div class="kes-tours-filter-actions">
                        <button
                            type="submit"
                            class="kes-v2-btn kes-v2-btn-gold"
                        >
                            {{ __('tours.apply_filters') }}
                        </button>

                        <a
                            href="{{ route(
                                'website.tours',
                                app()->isLocale('ur')
                                    ? ['lang' => 'ur']
                                    : [],
                            ) }}"
                            class="kes-tours-clear"
                        >
                            {{ __('tours.clear_filters') }}
                        </a>
                    </div>
                </form>

                <div class="kes-tours-results-heading">
                    <div>
                        <span class="kes-v2-section-label">
                            {{ __('tours.available_tours') }}
                        </span>

                        <h2>{{ __('tours.page_heading') }}</h2>
                    </div>

                    <strong>{{ $tours->total() }}</strong>
                </div>

                <div class="kes-tours-grid">
                    @forelse ($tours as $tour)
                        @php
                            $tourRouteParameters = [
                                'pakistanTour' => $tour,
                            ];

                            if (app()->isLocale('ur')) {
                                $tourRouteParameters['lang'] = 'ur';
                            }

                            $tourUrl = route(
                                'website.tours.show',
                                $tourRouteParameters,
                            );

                            $tourImage = $tour->cover_image
    ? asset('storage/'.$tour->cover_image)
    : null;

                            $priceNote = app()->isLocale('ur')
                                && filled($tour->price_note_ur)
                                    ? $tour->price_note_ur
                                    : $tour->price_note;
                        @endphp

                        <article class="kes-tour-card">
                            <a
                                href="{{ $tourUrl }}"
                                class="kes-tour-card-image"
                                aria-label="{{ $tour->localized_title }}"
                            >
                                @if ($tourImage)
    <img
        src="{{ $tourImage }}"
        alt="{{ $tour->localized_title }}"
        loading="lazy"
    >
@endif

                                @if ($tour->is_featured)
                                    <span class="kes-tour-featured-badge">
                                        {{ __('tours.featured') }}
                                    </span>
                                @endif
                            </a>

                            <div class="kes-tour-card-body">
                                <div class="kes-tour-card-location">
                                    <span>{{ $tour->destination }}</span>

                                    @if (filled($tour->province))
                                        <small>{{ $tour->province }}</small>
                                    @endif
                                </div>

                                <h2>
                                    <a href="{{ $tourUrl }}">
                                        {{ $tour->localized_title }}
                                    </a>
                                </h2>

                                @if (filled($tour->localized_subtitle))
                                    <p class="kes-tour-card-subtitle">
                                        {{ $tour->localized_subtitle }}
                                    </p>
                                @endif

                                @if (filled($tour->localized_short_description))
                                    <p class="kes-tour-card-description">
                                        {{ \Illuminate\Support\Str::limit(
                                            $tour->localized_short_description,
                                            130,
                                        ) }}
                                    </p>
                                @endif

                                <div class="kes-tour-card-meta">
                                    @if (filled($tour->duration_days))
                                        <div>
                                            <span>
                                                {{ __('tours.duration') }}
                                            </span>

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

                                    @if (filled($tour->seats_left))
                                        <div>
                                            <span>
                                                {{ __('tours.seats_left') }}
                                            </span>

                                            <strong>
                                                {{ number_format($tour->seats_left) }}
                                            </strong>
                                        </div>
                                    @endif
                                </div>

                                <div class="kes-tour-card-footer">
                                    <div class="kes-tour-price">
                                        <span>
                                            {{ __('tours.starting_from') }}
                                        </span>

                                        @if ((float) $tour->price > 0)
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

                                    <a
                                        href="{{ $tourUrl }}"
                                        class="kes-tour-card-link"
                                    >
                                        {{ __('tours.view_details') }}

                                        <span>
                                            {{ app()->isLocale('ur')
                                                ? '←'
                                                : '→' }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="kes-tours-empty">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="Karwan E Al Saeed Travel & Tours"
                            >

                            <h2>{{ __('tours.no_tours_title') }}</h2>

                            <p>{{ __('tours.no_tours_text') }}</p>

                            <a
                                href="{{ route(
                                    'website.tours',
                                    app()->isLocale('ur')
                                        ? ['lang' => 'ur']
                                        : [],
                                ) }}"
                                class="kes-v2-btn kes-v2-btn-gold"
                            >
                                {{ __('tours.clear_filters') }}
                            </a>
                        </div>
                    @endforelse
                </div>

                @if ($tours->hasPages())
                    <div class="kes-tours-pagination">
                        {{ $tours->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>
@endsection