@extends('website.layout')

@section('content')
    <main class="kes-umrah-list-v2">

        <section class="kes-umrah-list-hero">
            <div class="kes-umrah-list-pattern"></div>

            <div class="kes-v2-container kes-umrah-list-hero-inner">
                <div>
                    <nav class="kes-page-v2-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route('website.home') }}">
                            {{ __('umrah.home') }}
                        </a>

                        <span>/</span>
                        <span>{{ __('umrah.umrah_packages') }}</span>
                    </nav>

                    <span class="kes-page-v2-label">
                        {{ __('umrah.hero_label') }}
                    </span>

                    <h1>{{ __('umrah.umrah_packages') }}</h1>

                    <p>{{ __('umrah.hero_description') }}</p>
                </div>

                <div class="kes-umrah-list-hero-logo">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Karwan E Al Saeed Travel & Tours">
                </div>
            </div>
        </section>

        <section class="kes-umrah-list-body">
            <div class="kes-v2-container">

                <div class="kes-umrah-filter-card">
                    <div class="kes-umrah-filter-heading">
                        <div>
                            <span class="kes-v2-section-label">
                                {{ __('umrah.find_your_package') }}
                            </span>

                            <h2>{{ __('umrah.search_available_packages') }}</h2>
                        </div>

                        <span class="kes-umrah-result-count">
                            {{ __('umrah.packages_found', ['count' => $packages->count()]) }}
                        </span>
                    </div>

                    <form method="GET" action="{{ route('website.umrah') }}">
                        <div class="kes-umrah-filter-grid">
                            <label>
                                <span>{{ __('umrah.search') }}</span>

                                <input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="{{ __('umrah.search_placeholder') }}"
                                >
                            </label>

                            <label>
                                <span>{{ __('umrah.airline') }}</span>

                                <select name="airline">
                                    <option value="">
                                        {{ __('umrah.all_airlines') }}
                                    </option>

                                    @foreach ($airlines as $airline)
                                        <option value="{{ $airline }}"
                                            @selected(request('airline') === $airline)>
                                            {{ $airline }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span>{{ __('umrah.route') }}</span>

                                <select name="route">
                                    <option value="">
                                        {{ __('umrah.all_routes') }}
                                    </option>

                                    @foreach ($routes as $route)
                                        <option value="{{ $route }}"
                                            @selected(request('route') === $route)>
                                            {{ $route }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span>{{ __('umrah.duration') }}</span>

                                <select name="duration">
                                    <option value="">
                                        {{ __('umrah.all_durations') }}
                                    </option>

                                    <option value="1-10" @selected(request('duration') === '1-10')>
                                        1–10 {{ __('umrah.nights') }}
                                    </option>

                                    <option value="11-20" @selected(request('duration') === '11-20')>
                                        11–20 {{ __('umrah.nights') }}
                                    </option>

                                    <option value="21-30" @selected(request('duration') === '21-30')>
                                        21–30 {{ __('umrah.nights') }}
                                    </option>

                                    <option value="31+" @selected(request('duration') === '31+')>
                                        31+ {{ __('umrah.nights') }}
                                    </option>
                                </select>
                            </label>

                            <label>
                                <span>{{ __('umrah.sort_by') }}</span>

                                <select name="sort">
                                    <option value="">
                                        {{ __('umrah.recommended') }}
                                    </option>

                                    <option value="latest" @selected(request('sort') === 'latest')>
                                        {{ __('umrah.latest') }}
                                    </option>

                                    <option value="price_low" @selected(request('sort') === 'price_low')>
                                        {{ __('umrah.price_low_high') }}
                                    </option>

                                    <option value="price_high" @selected(request('sort') === 'price_high')>
                                        {{ __('umrah.price_high_low') }}
                                    </option>

                                    <option value="duration_low" @selected(request('sort') === 'duration_low')>
                                        {{ __('umrah.duration_low_high') }}
                                    </option>

                                    <option value="duration_high" @selected(request('sort') === 'duration_high')>
                                        {{ __('umrah.duration_high_low') }}
                                    </option>
                                </select>
                            </label>
                        </div>

                        <div class="kes-umrah-filter-actions">
                            <button type="submit" class="kes-v2-btn kes-v2-btn-gold">
                                {{ __('umrah.apply_filters') }}
                            </button>

                            <a href="{{ route('website.umrah') }}"
                               class="kes-umrah-reset-btn">
                                {{ __('umrah.reset_filters') }}
                            </a>
                        </div>
                    </form>
                </div>

                @if ($packages->count() > 0)
                    <div class="kes-umrah-package-grid">
                        @foreach ($packages as $package)
                            @php
                                $group = $package->umrahGroupFlight;

                                $segments = is_array($group?->flight_segments)
                                    ? array_values($group->flight_segments)
                                    : [];

                                $firstSegment = $segments[0] ?? [];

                                $lastSegment = $segments !== []
                                    ? $segments[array_key_last($segments)]
                                    : [];

                                $routeText = $package->flight_route
                                    ?: ($group?->route ?: '-');

                                $routeParts = collect(
                                    explode('-', str_replace(' ', '', $routeText))
                                )->filter()->values();

                                $fromCode = $routeParts[0]
                                    ?? ($firstSegment['from'] ?? 'MUX');

                                $toCode = $routeParts[1]
                                    ?? ($firstSegment['to'] ?? 'JED');

                                $backCode = $routeParts[2]
                                    ?? ($lastSegment['to'] ?? $fromCode);

                                $departureDate = $group?->departure_date
                                    ? $group->departure_date->format('d M Y')
                                    : __('umrah.to_be_announced');

                                $returnDate = $group?->return_date
                                    ? $group->return_date->format('d M Y')
                                    : __('umrah.to_be_announced');

                                $departureTime = $firstSegment['departure_time'] ?? '-';
                                $returnTime = $lastSegment['departure_time'] ?? '-';

                                $baggage = $group?->baggage
                                    ?: __('umrah.contact_for_details');

                                $meals = $group?->meals === 'YES'
                                    ? __('umrah.included')
                                    : __('umrah.not_included');

                                $whatsappText = __('umrah.details_whatsapp_message', [
                                    'number' => $package->package_no,
                                    'title' => $package->title,
                                ]);
                            @endphp

                            <article class="kes-umrah-package-card">
                                <div class="kes-umrah-package-top">
                                    <div>
                                        <span class="kes-umrah-package-code">
                                            {{ $package->package_no }}
                                        </span>

                                        <h2>{{ $package->title }}</h2>

                                        <p>
                                            {{ $package->airline ?: __('umrah.umrah_travel_package') }}
                                            &bull;
                                            {{ $package->group_code ?: __('umrah.group_details_available') }}
                                        </p>
                                    </div>

                                    <div class="kes-umrah-package-logo">
                                        @if ($package->package_image)
                                            <img
                                                src="{{ asset('storage/'.$package->package_image) }}"
                                                alt="{{ $package->title }}"
                                            >
                                        @else
                                            <img
                                                src="{{ asset('images/logo.png') }}"
                                                alt="Karwan E Al Saeed"
                                            >
                                        @endif
                                    </div>
                                </div>

                                <div class="kes-umrah-route-summary">
                                    <div>
                                        <span>{{ __('umrah.from') }}</span>
                                        <strong>{{ $fromCode }}</strong>
                                    </div>

                                    <div class="kes-umrah-route-line">
                                        <span>
                                            {{ $package->duration_nights ?: '-' }}
                                            {{ __('umrah.nights') }}
                                        </span>
                                    </div>

                                    <div>
                                        <span>{{ __('umrah.destination') }}</span>
                                        <strong>{{ $toCode }}</strong>
                                    </div>

                                    <div>
                                        <span>{{ __('umrah.return') }}</span>
                                        <strong>{{ $backCode }}</strong>
                                    </div>
                                </div>

                                <div class="kes-umrah-date-grid">
                                    <div>
                                        <span>{{ __('umrah.departure') }}</span>
                                        <strong>{{ $departureDate }}</strong>
                                        <small>{{ $departureTime }}</small>
                                    </div>

                                    <div>
                                        <span>{{ __('umrah.return') }}</span>
                                        <strong>{{ $returnDate }}</strong>
                                        <small>{{ $returnTime }}</small>
                                    </div>

                                    <div>
                                        <span>{{ __('umrah.baggage') }}</span>
                                        <strong>{{ $baggage }}</strong>
                                        <small>{{ __('umrah.airline_policy') }}</small>
                                    </div>

                                    <div>
                                        <span>{{ __('umrah.meals') }}</span>
                                        <strong>{{ $meals }}</strong>
                                        <small>{{ __('umrah.flight_meal_information') }}</small>
                                    </div>
                                </div>

                                <div class="kes-umrah-hotel-grid">
                                    <div>
                                        <span>{{ __('umrah.makkah_hotel') }}</span>

                                        <h3>
                                            {{ $package->makkah_hotel
                                                ?: __('umrah.details_available_soon') }}
                                        </h3>

                                        <p>
                                            {{ $package->makkah_nights ?: '-' }}
                                            {{ __('umrah.nights') }}
                                            &bull;
                                            {{ $package->makkah_distance
                                                ?: __('umrah.distance_on_request') }}
                                        </p>
                                    </div>

                                    <div>
                                        <span>{{ __('umrah.madinah_hotel') }}</span>

                                        <h3>
                                            {{ $package->madina_hotel
                                                ?: __('umrah.details_available_soon') }}
                                        </h3>

                                        <p>
                                            {{ $package->madina_nights ?: '-' }}
                                            {{ __('umrah.nights') }}
                                            &bull;
                                            {{ $package->madina_distance
                                                ?: __('umrah.distance_on_request') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="kes-umrah-transport-row">
                                    <span>{{ __('umrah.transport') }}</span>
                                    <strong>{{ $package->transport ?: $routeText }}</strong>
                                </div>

                                <div class="kes-umrah-price-grid">
                                    <div>
                                        <span>{{ __('umrah.sharing') }}</span>

                                        <strong>
                                            {{ $package->sharing_price
                                                ? 'PKR '.number_format((float) $package->sharing_price, 0)
                                                : __('umrah.on_request') }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>{{ __('umrah.quad') }}</span>

                                        <strong>
                                            {{ $package->quad_price
                                                ? 'PKR '.number_format((float) $package->quad_price, 0)
                                                : __('umrah.on_request') }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>{{ __('umrah.triple') }}</span>

                                        <strong>
                                            {{ $package->triple_price
                                                ? 'PKR '.number_format((float) $package->triple_price, 0)
                                                : __('umrah.on_request') }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>{{ __('umrah.double') }}</span>

                                        <strong>
                                            {{ $package->double_price
                                                ? 'PKR '.number_format((float) $package->double_price, 0)
                                                : __('umrah.on_request') }}
                                        </strong>
                                    </div>
                                </div>

                                <div class="kes-umrah-package-footer">
                                    <div class="kes-umrah-seat-status">
                                        <span></span>

                                        {{ __('umrah.seats_available_sentence', [
                                            'left' => $package->seats_left,
                                            'total' => $package->seats_available,
                                        ]) }}
                                    </div>

                                    <div class="kes-umrah-package-actions">
                                        <a href="{{ route('website.umrah.show', $package) }}"
                                           class="kes-umrah-details-btn">
                                            {{ __('umrah.view_details') }}
                                        </a>

                                        <a
                                            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}?text={{ urlencode($whatsappText) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="kes-umrah-whatsapp-btn"
                                        >
                                            {{ __('umrah.ask_whatsapp') }}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="kes-umrah-empty-state">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="Karwan E Al Saeed">

                        <span class="kes-v2-section-label">
                            {{ __('umrah.no_results') }}
                        </span>

                        <h2>{{ __('umrah.no_package_found') }}</h2>

                        <p>{{ __('umrah.no_package_text') }}</p>

                        <a href="{{ route('website.umrah') }}"
                           class="kes-v2-btn kes-v2-btn-gold">
                            {{ __('umrah.view_all_packages') }}
                        </a>
                    </div>
                @endif

            </div>
        </section>

    </main>
@endsection