@extends('website.layout')

@section('content')
    <main class="kes-umrah-list-v2">

        <section class="kes-umrah-list-hero">
            <div class="kes-umrah-list-pattern"></div>

            <div class="kes-v2-container kes-umrah-list-hero-inner">
                <div>
                    <nav class="kes-page-v2-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route('website.home') }}">Home</a>
                        <span>/</span>
                        <span>Umrah Packages</span>
                    </nav>

                    <span class="kes-page-v2-label">
                        Sacred Journeys With Professional Support
                    </span>

                    <h1>Umrah Packages</h1>

                    <p>
                        Explore our latest Umrah packages with flight, hotel,
                        transport and accommodation details.
                    </p>
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
                            <span class="kes-v2-section-label">Find Your Package</span>
                            <h2>Search Available Umrah Packages</h2>
                        </div>

                        <span class="kes-umrah-result-count">
                            {{ $packages->count() }} Package(s) Found
                        </span>
                    </div>

                    <form method="GET" action="{{ route('website.umrah') }}">
                        <div class="kes-umrah-filter-grid">
                            <label>
                                <span>Search</span>
                                <input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Package, hotel or route"
                                >
                            </label>

                            <label>
                                <span>Airline</span>
                                <select name="airline">
                                    <option value="">All Airlines</option>

                                    @foreach ($airlines as $airline)
                                        <option value="{{ $airline }}"
                                            @selected(request('airline') === $airline)>
                                            {{ $airline }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span>Route</span>
                                <select name="route">
                                    <option value="">All Routes</option>

                                    @foreach ($routes as $route)
                                        <option value="{{ $route }}"
                                            @selected(request('route') === $route)>
                                            {{ $route }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span>Duration</span>
                                <select name="duration">
                                    <option value="">All Durations</option>
                                    <option value="1-10" @selected(request('duration') === '1-10')>1–10 Nights</option>
                                    <option value="11-20" @selected(request('duration') === '11-20')>11–20 Nights</option>
                                    <option value="21-30" @selected(request('duration') === '21-30')>21–30 Nights</option>
                                    <option value="31+" @selected(request('duration') === '31+')>31+ Nights</option>
                                </select>
                            </label>

                            <label>
                                <span>Sort By</span>
                                <select name="sort">
                                    <option value="">Recommended</option>
                                    <option value="latest" @selected(request('sort') === 'latest')>Latest</option>
                                    <option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High</option>
                                    <option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low</option>
                                    <option value="duration_low" @selected(request('sort') === 'duration_low')>Duration: Low to High</option>
                                    <option value="duration_high" @selected(request('sort') === 'duration_high')>Duration: High to Low</option>
                                </select>
                            </label>
                        </div>

                        <div class="kes-umrah-filter-actions">
                            <button type="submit" class="kes-v2-btn kes-v2-btn-gold">
                                Apply Filters
                            </button>

                            <a href="{{ route('website.umrah') }}"
                               class="kes-umrah-reset-btn">
                                Reset Filters
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

                                $routeText = $package->flight_route ?: ($group?->route ?: '-');

                                $routeParts = collect(
                                    explode('-', str_replace(' ', '', $routeText))
                                )->filter()->values();

                                $fromCode = $routeParts[0] ?? ($firstSegment['from'] ?? 'MUX');
                                $toCode = $routeParts[1] ?? ($firstSegment['to'] ?? 'JED');
                                $backCode = $routeParts[2] ?? ($lastSegment['to'] ?? $fromCode);

                                $departureDate = $group?->departure_date
                                    ? $group->departure_date->format('d M Y')
                                    : 'To Be Announced';

                                $returnDate = $group?->return_date
                                    ? $group->return_date->format('d M Y')
                                    : 'To Be Announced';

                                $departureTime = $firstSegment['departure_time'] ?? '-';
                                $returnTime = $lastSegment['departure_time'] ?? '-';
                                $baggage = $group?->baggage ?: 'Contact for details';
                                $meals = $group?->meals === 'YES' ? 'Included' : 'Not Included';

                                $whatsappText = 'Assalam o Alaikum, mujhe Umrah Package '
                                    . $package->package_no . ' - '
                                    . $package->title
                                    . ' ke baare mein details chahiye.';
                            @endphp

                            <article class="kes-umrah-package-card">
                                <div class="kes-umrah-package-top">
                                    <div>
                                        <span class="kes-umrah-package-code">
                                            {{ $package->package_no }}
                                        </span>

                                        <h2>{{ $package->title }}</h2>

                                        <p>
                                            {{ $package->airline ?: 'Umrah Travel Package' }}
                                            &bull;
                                            {{ $package->group_code ?: 'Group details available' }}
                                        </p>
                                    </div>

                                    <div class="kes-umrah-package-logo">
                                        @if ($package->package_image)
                                            <img
                                                src="{{ asset('storage/' . $package->package_image) }}"
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
                                        <span>From</span>
                                        <strong>{{ $fromCode }}</strong>
                                    </div>

                                    <div class="kes-umrah-route-line">
                                        <span>{{ $package->duration_nights ?: '-' }} Nights</span>
                                    </div>

                                    <div>
                                        <span>Destination</span>
                                        <strong>{{ $toCode }}</strong>
                                    </div>

                                    <div>
                                        <span>Return</span>
                                        <strong>{{ $backCode }}</strong>
                                    </div>
                                </div>

                                <div class="kes-umrah-date-grid">
                                    <div>
                                        <span>Departure</span>
                                        <strong>{{ $departureDate }}</strong>
                                        <small>{{ $departureTime }}</small>
                                    </div>

                                    <div>
                                        <span>Return</span>
                                        <strong>{{ $returnDate }}</strong>
                                        <small>{{ $returnTime }}</small>
                                    </div>

                                    <div>
                                        <span>Baggage</span>
                                        <strong>{{ $baggage }}</strong>
                                        <small>Per airline policy</small>
                                    </div>

                                    <div>
                                        <span>Meals</span>
                                        <strong>{{ $meals }}</strong>
                                        <small>Flight meal information</small>
                                    </div>
                                </div>

                                <div class="kes-umrah-hotel-grid">
                                    <div>
                                        <span>Makkah Hotel</span>
                                        <h3>{{ $package->makkah_hotel ?: 'Details Available Soon' }}</h3>

                                        <p>
                                            {{ $package->makkah_nights ?: '-' }} Nights
                                            &bull;
                                            {{ $package->makkah_distance ?: 'Distance on request' }}
                                        </p>
                                    </div>

                                    <div>
                                        <span>Madinah Hotel</span>
                                        <h3>{{ $package->madina_hotel ?: 'Details Available Soon' }}</h3>

                                        <p>
                                            {{ $package->madina_nights ?: '-' }} Nights
                                            &bull;
                                            {{ $package->madina_distance ?: 'Distance on request' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="kes-umrah-transport-row">
                                    <span>Transport</span>
                                    <strong>{{ $package->transport ?: $routeText }}</strong>
                                </div>

                                <div class="kes-umrah-price-grid">
                                    <div>
                                        <span>Sharing</span>
                                        <strong>
                                            {{ $package->sharing_price
                                                ? 'PKR ' . number_format((float) $package->sharing_price, 0)
                                                : 'On Request' }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>Quad</span>
                                        <strong>
                                            {{ $package->quad_price
                                                ? 'PKR ' . number_format((float) $package->quad_price, 0)
                                                : 'On Request' }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>Triple</span>
                                        <strong>
                                            {{ $package->triple_price
                                                ? 'PKR ' . number_format((float) $package->triple_price, 0)
                                                : 'On Request' }}
                                        </strong>
                                    </div>

                                    <div>
                                        <span>Double</span>
                                        <strong>
                                            {{ $package->double_price
                                                ? 'PKR ' . number_format((float) $package->double_price, 0)
                                                : 'On Request' }}
                                        </strong>
                                    </div>
                                </div>

                                <div class="kes-umrah-package-footer">
                                    <div class="kes-umrah-seat-status">
                                        <span></span>
                                        {{ $package->seats_left }} of
                                        {{ $package->seats_available }} seats available
                                    </div>

                                    <div class="kes-umrah-package-actions">
                                        <a href="{{ route('website.umrah.show', $package) }}"
                                           class="kes-umrah-details-btn">
                                            View Details
                                        </a>

                                        <a
                                            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}?text={{ urlencode($whatsappText) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="kes-umrah-whatsapp-btn"
                                        >
                                            Ask on WhatsApp
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

                        <span class="kes-v2-section-label">No Results</span>

                        <h2>No Umrah Package Found</h2>

                        <p>
                            Is filter ke mutabiq koi package nahi mila.
                            Filters reset karke dobara search karein.
                        </p>

                        <a href="{{ route('website.umrah') }}"
                           class="kes-v2-btn kes-v2-btn-gold">
                            View All Packages
                        </a>
                    </div>
                @endif

            </div>
        </section>

    </main>
@endsection