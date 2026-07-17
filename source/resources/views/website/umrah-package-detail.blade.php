@extends('website.layout')

@section('content')
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

        $services = is_array($package->included_services)
            ? $package->included_services
            : [];

        if ($package->other_included_service) {
            $services[] = $package->other_included_service;
        }

        $giftedServices = is_array($package->gifted_services)
            ? $package->gifted_services
            : [];

        $prices = $package->prices_list ?? [];

        $whatsappText = 'Assalam o Alaikum, mujhe Umrah Package '
            . $package->package_no . ' - '
            . $package->title
            . ' book karna hai.';
    @endphp

    <main class="kes-umrah-detail-v2">

        <section class="kes-umrah-detail-hero">
            <div class="kes-umrah-detail-pattern"></div>

            <div class="kes-v2-container kes-umrah-detail-hero-inner">
                <div class="kes-umrah-detail-hero-content">
                    <nav class="kes-page-v2-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route('website.home') }}">Home</a>
                        <span>/</span>
                        <a href="{{ route('website.umrah') }}">Umrah Packages</a>
                        <span>/</span>
                        <span>{{ $package->package_no }}</span>
                    </nav>

                    <span class="kes-umrah-detail-code">
                        {{ $package->package_no }}
                    </span>

                    <h1>{{ $package->title }}</h1>

                    <p>
                        {{ $package->subtitle ?: 'Complete Umrah package details with flight, hotel, transport and accommodation information.' }}
                    </p>

                    <div class="kes-umrah-detail-meta">
                        <div>
                            <span>Airline</span>
                            <strong>{{ $package->airline ?: 'To Be Confirmed' }}</strong>
                        </div>

                        <div>
                            <span>Route</span>
                            <strong>{{ $routeText }}</strong>
                        </div>

                        <div>
                            <span>Duration</span>
                            <strong>{{ $package->duration_nights ?: '-' }} Nights</strong>
                        </div>

                        <div>
                            <span>Group</span>
                            <strong>{{ $package->group_code ?: 'Open Group' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="kes-umrah-detail-hero-logo">
                    @if ($package->package_image)
                        <img
                            src="{{ asset('storage/' . $package->package_image) }}"
                            alt="{{ $package->title }}"
                        >
                    @else
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="Karwan E Al Saeed Travel & Tours"
                        >
                    @endif
                </div>
            </div>
        </section>

        <section class="kes-umrah-detail-body">
            <div class="kes-v2-container kes-umrah-detail-layout">

                <div class="kes-umrah-detail-main">

                    @if ($package->description)
                        <section class="kes-umrah-detail-section">
                            <span class="kes-v2-section-label">Package Overview</span>
                            <h2>About This Umrah Package</h2>
                            <p class="kes-umrah-detail-description">
                                {{ $package->description }}
                            </p>
                        </section>
                    @endif

                    <section class="kes-umrah-detail-section">
                        <div class="kes-umrah-detail-section-heading">
                            <div>
                                <span class="kes-v2-section-label">Journey Schedule</span>
                                <h2>Travel Information</h2>
                            </div>
                        </div>

                        <div class="kes-umrah-detail-summary-grid">
                            <article>
                                <span>Departure</span>
                                <strong>
                                    {{ $group?->departure_date
                                        ? $group->departure_date->format('d M Y')
                                        : 'To Be Announced' }}
                                </strong>
                            </article>

                            <article>
                                <span>Return</span>
                                <strong>
                                    {{ $group?->return_date
                                        ? $group->return_date->format('d M Y')
                                        : 'To Be Announced' }}
                                </strong>
                            </article>

                            <article>
                                <span>Duration</span>
                                <strong>{{ $package->duration_nights ?: '-' }} Nights</strong>
                            </article>

                            <article>
                                <span>Availability</span>
                                <strong>
                                    {{ $package->seats_left }} /
                                    {{ $package->seats_available }} Seats
                                </strong>
                            </article>
                        </div>
                    </section>

                    <section class="kes-umrah-detail-section">
                        <div class="kes-umrah-detail-section-heading">
                            <div>
                                <span class="kes-v2-section-label">Flight Schedule</span>
                                <h2>Flight Details</h2>
                            </div>

                            <span class="kes-umrah-detail-route">
                                {{ $fromCode }} &rarr; {{ $toCode }} &rarr; {{ $backCode }}
                            </span>
                        </div>

                        <div class="kes-umrah-flight-list">
                            @forelse ($segments as $index => $segment)
                                <article class="kes-umrah-flight-card">
                                    <div class="kes-umrah-flight-number">
                                        <span>Flight {{ $index + 1 }}</span>
                                        <strong>{{ $segment['flight_no'] ?? 'Flight Details' }}</strong>
                                    </div>

                                    <div class="kes-umrah-flight-route">
                                        <div>
                                            <span>From</span>
                                            <strong>{{ $segment['from'] ?? '-' }}</strong>
                                        </div>

                                        <div class="kes-umrah-flight-line">
                                            <span>Flight Route</span>
                                        </div>

                                        <div>
                                            <span>To</span>
                                            <strong>{{ $segment['to'] ?? '-' }}</strong>
                                        </div>
                                    </div>

                                    <div class="kes-umrah-flight-time">
                                        <div>
                                            <span>Date</span>
                                            <strong>
                                                {{ filled($segment['departure_date'] ?? null)
                                                    ? \Carbon\Carbon::parse($segment['departure_date'])->format('d M Y')
                                                    : '-' }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>Departure Time</span>
                                            <strong>{{ $segment['departure_time'] ?? '-' }}</strong>
                                        </div>

                                        <div>
                                            <span>Arrival Time</span>
                                            <strong>{{ $segment['arrival_time'] ?? '-' }}</strong>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <article class="kes-umrah-flight-card">
                                    <div class="kes-umrah-flight-number">
                                        <span>Flight Route</span>
                                        <strong>{{ $routeText }}</strong>
                                    </div>

                                    <div class="kes-umrah-flight-route">
                                        <div>
                                            <span>From</span>
                                            <strong>{{ $fromCode }}</strong>
                                        </div>

                                        <div class="kes-umrah-flight-line">
                                            <span>Route</span>
                                        </div>

                                        <div>
                                            <span>Destination</span>
                                            <strong>{{ $toCode }}</strong>
                                        </div>
                                    </div>
                                </article>
                            @endforelse
                        </div>
                    </section>

                    <section class="kes-umrah-detail-section">
                        <div class="kes-umrah-detail-section-heading">
                            <div>
                                <span class="kes-v2-section-label">Accommodation</span>
                                <h2>Hotel Details</h2>
                            </div>
                        </div>

                        <div class="kes-umrah-detail-hotel-grid">
                            <article>
                                <span>Makkah Accommodation</span>
                                <h3>{{ $package->makkah_hotel ?: 'Hotel Details Available Soon' }}</h3>

                                <div>
                                    <p>
                                        <small>Nights</small>
                                        <strong>{{ $package->makkah_nights ?: '-' }}</strong>
                                    </p>

                                    <p>
                                        <small>Distance</small>
                                        <strong>{{ $package->makkah_distance ?: '-' }}</strong>
                                    </p>

                                    <p>
                                        <small>Location</small>
                                        <strong>{{ $package->makkah_location ?: '-' }}</strong>
                                    </p>
                                </div>
                            </article>

                            <article>
                                <span>Madinah Accommodation</span>
                                <h3>{{ $package->madina_hotel ?: 'Hotel Details Available Soon' }}</h3>

                                <div>
                                    <p>
                                        <small>Nights</small>
                                        <strong>{{ $package->madina_nights ?: '-' }}</strong>
                                    </p>

                                    <p>
                                        <small>Distance</small>
                                        <strong>{{ $package->madina_distance ?: '-' }}</strong>
                                    </p>

                                    <p>
                                        <small>Location</small>
                                        <strong>{{ $package->madina_location ?: '-' }}</strong>
                                    </p>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="kes-umrah-detail-section">
                        <div class="kes-umrah-detail-section-heading">
                            <div>
                                <span class="kes-v2-section-label">Ground Services</span>
                                <h2>Transport Information</h2>
                            </div>
                        </div>

                        <div class="kes-umrah-detail-transport">
                            <span>Package Transport</span>
                            <strong>{{ $package->transport ?: $routeText }}</strong>
                        </div>
                    </section>

                    @if (count($services) > 0)
                        <section class="kes-umrah-detail-section">
                            <div class="kes-umrah-detail-section-heading">
                                <div>
                                    <span class="kes-v2-section-label">Package Benefits</span>
                                    <h2>Included Services</h2>
                                </div>
                            </div>

                            <div class="kes-umrah-detail-service-grid">
                                @foreach ($services as $service)
                                    <div>
                                        <span></span>
                                        <strong>{{ $service }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if (count($giftedServices) > 0)
                        <section class="kes-umrah-detail-section">
                            <div class="kes-umrah-detail-section-heading">
                                <div>
                                    <span class="kes-v2-section-label">Complimentary Benefits</span>
                                    <h2>Gifted Services</h2>
                                </div>
                            </div>

                            <div class="kes-umrah-detail-gift-grid">
                                @foreach ($giftedServices as $service)
                                    <div>
                                        <span>Gift</span>
                                        <strong>{{ $service }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="kes-umrah-detail-section">
                        <div class="kes-umrah-detail-section-heading">
                            <div>
                                <span class="kes-v2-section-label">Accommodation Prices</span>
                                <h2>Price Per Person</h2>
                            </div>
                        </div>

                        <div class="kes-umrah-detail-price-grid">
                            @forelse ($prices as $label => $amount)
                                <article>
                                    <span>{{ $label }}</span>

                                    <strong>
                                        {{ $amount
                                            ? 'PKR ' . number_format((float) $amount, 0)
                                            : 'On Request' }}
                                    </strong>

                                    <small>Per person</small>
                                </article>
                            @empty
                                <article>
                                    <span>Package Price</span>
                                    <strong>Contact for Price</strong>
                                    <small>Latest rate available on request</small>
                                </article>
                            @endforelse
                        </div>
                    </section>

                </div>

                <aside class="kes-umrah-detail-sidebar">
                    <div class="kes-umrah-booking-card">
                        <span class="kes-v2-section-label">Package Booking</span>
                        <h2>Reserve Your Umrah Package</h2>

                        <p>
                            Contact our team for seat confirmation, required documents,
                            payment details and complete booking guidance.
                        </p>

                        <div class="kes-umrah-booking-availability">
                            <div>
                                <span>Seats Available</span>
                                <strong>{{ $package->seats_left }}</strong>
                            </div>

                            <div>
                                <span>Total Seats</span>
                                <strong>{{ $package->seats_available }}</strong>
                            </div>
                        </div>

                        <a
                            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}?text={{ urlencode($whatsappText) }}"
                            target="_blank"
                            rel="noopener"
                            class="kes-umrah-booking-whatsapp"
                        >
                            Book on WhatsApp
                        </a>

                        <a href="{{ route('website.contact') }}"
                           class="kes-umrah-booking-contact">
                            Send Website Inquiry
                        </a>
                    </div>

                    <div class="kes-umrah-detail-help-card">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="Karwan E Al Saeed">

                        <h3>Need More Information?</h3>

                        <p>
                            Our travel team will guide you regarding flights,
                            hotels, documents and package availability.
                        </p>

                        <a href="{{ route('website.umrah') }}">
                            View All Packages
                        </a>
                    </div>
                </aside>

            </div>
        </section>

    </main>
@endsection