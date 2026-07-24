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

        $whatsappText = __('umrah.booking_whatsapp_message', [
            'number' => $package->package_no,
            'title' => $package->title,
        ]);
    @endphp

    <main class="kes-umrah-detail-v2">

        <section class="kes-umrah-detail-hero">
            <div class="kes-umrah-detail-pattern"></div>

            <div class="kes-v2-container kes-umrah-detail-hero-inner">
                <div class="kes-umrah-detail-hero-content">
                    <nav class="kes-page-v2-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route('website.home') }}">
                            {{ __('umrah.home') }}
                        </a>

                        <span>/</span>

                        <a href="{{ route('website.umrah') }}">
                            {{ __('umrah.umrah_packages') }}
                        </a>

                        <span>/</span>
                        <span>{{ $package->package_no }}</span>
                    </nav>

                    <span class="kes-umrah-detail-code">
                        {{ $package->package_no }}
                    </span>

                    <h1>{{ $package->title }}</h1>

                    <p>
                        {{ $package->subtitle
                            ?: __('umrah.default_package_description') }}
                    </p>

                    <div class="kes-umrah-detail-meta">
                        <div>
                            <span>{{ __('umrah.airline') }}</span>

                            <strong>
                                {{ $package->airline
                                    ?: __('umrah.to_be_confirmed') }}
                            </strong>
                        </div>

                        <div>
                            <span>{{ __('umrah.route') }}</span>
                            <strong>{{ $routeText }}</strong>
                        </div>

                        <div>
                            <span>{{ __('umrah.duration') }}</span>

                            <strong>
                                {{ $package->duration_nights ?: '-' }}
                                {{ __('umrah.nights') }}
                            </strong>
                        </div>

                        <div>
                            <span>{{ __('umrah.group') }}</span>

                            <strong>
                                {{ $package->group_code
                                    ?: __('umrah.open_group') }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="kes-umrah-detail-hero-logo">
                    @if ($package->package_image)
                        <img
                            src="{{ asset('storage/'.$package->package_image) }}"
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
                            <span class="kes-v2-section-label">
                                {{ __('umrah.package_overview') }}
                            </span>

                            <h2>{{ __('umrah.about_package') }}</h2>

                            <p class="kes-umrah-detail-description">
                                {{ $package->description }}
                            </p>
                        </section>
                    @endif

                    <section class="kes-umrah-detail-section">
                        <div class="kes-umrah-detail-section-heading">
                            <div>
                                <span class="kes-v2-section-label">
                                    {{ __('umrah.journey_schedule') }}
                                </span>

                                <h2>{{ __('umrah.travel_information') }}</h2>
                            </div>
                        </div>

                        <div class="kes-umrah-detail-summary-grid">
                            <article>
                                <span>{{ __('umrah.departure') }}</span>

                                <strong>
                                    {{ $group?->departure_date
                                        ? $group->departure_date->format('d M Y')
                                        : __('umrah.to_be_announced') }}
                                </strong>
                            </article>

                            <article>
                                <span>{{ __('umrah.return') }}</span>

                                <strong>
                                    {{ $group?->return_date
                                        ? $group->return_date->format('d M Y')
                                        : __('umrah.to_be_announced') }}
                                </strong>
                            </article>

                            <article>
                                <span>{{ __('umrah.duration') }}</span>

                                <strong>
                                    {{ $package->duration_nights ?: '-' }}
                                    {{ __('umrah.nights') }}
                                </strong>
                            </article>

                            <article>
                                <span>{{ __('umrah.availability') }}</span>

                                <strong>
                                    {{ $package->seats_left }} /
                                    {{ $package->seats_available }}
                                    {{ __('umrah.seats') }}
                                </strong>
                            </article>
                        </div>
                    </section>

                    <section class="kes-umrah-detail-section">
                        <div class="kes-umrah-detail-section-heading">
                            <div>
                                <span class="kes-v2-section-label">
                                    {{ __('umrah.flight_schedule') }}
                                </span>

                                <h2>{{ __('umrah.flight_details') }}</h2>
                            </div>

                            <span class="kes-umrah-detail-route">
                                {{ $fromCode }}
                                {{ app()->isLocale('ur') ? '←' : '→' }}
                                {{ $toCode }}
                                {{ app()->isLocale('ur') ? '←' : '→' }}
                                {{ $backCode }}
                            </span>
                        </div>

                        <div class="kes-umrah-flight-list">
                            @forelse ($segments as $index => $segment)
                                <article class="kes-umrah-flight-card">
                                    <div class="kes-umrah-flight-number">
                                        <span>
                                            {{ __('umrah.flight', [
                                                'number' => $index + 1,
                                            ]) }}
                                        </span>

                                        <strong>
                                            {{ $segment['flight_no']
                                                ?? __('umrah.flight_details') }}
                                        </strong>
                                    </div>

                                    <div class="kes-umrah-flight-route">
                                        <div>
                                            <span>{{ __('umrah.from') }}</span>

                                            <strong>
                                                {{ $segment['from'] ?? '-' }}
                                            </strong>
                                        </div>

                                        <div class="kes-umrah-flight-line">
                                            <span>{{ __('umrah.flight_route') }}</span>
                                        </div>

                                        <div>
                                            <span>{{ __('umrah.to') }}</span>

                                            <strong>
                                                {{ $segment['to'] ?? '-' }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="kes-umrah-flight-time">
                                        <div>
                                            <span>{{ __('umrah.date') }}</span>

                                            <strong>
                                                {{ filled($segment['departure_date'] ?? null)
                                                    ? \Carbon\Carbon::parse($segment['departure_date'])->format('d M Y')
                                                    : '-' }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>{{ __('umrah.departure_time') }}</span>

                                            <strong>
                                                {{ $segment['departure_time'] ?? '-' }}
                                            </strong>
                                        </div>

                                        <div>
                                            <span>{{ __('umrah.arrival_time') }}</span>

                                            <strong>
                                                {{ $segment['arrival_time'] ?? '-' }}
                                            </strong>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <article class="kes-umrah-flight-card">
                                    <div class="kes-umrah-flight-number">
                                        <span>{{ __('umrah.flight_route') }}</span>
                                        <strong>{{ $routeText }}</strong>
                                    </div>

                                    <div class="kes-umrah-flight-route">
                                        <div>
                                            <span>{{ __('umrah.from') }}</span>
                                            <strong>{{ $fromCode }}</strong>
                                        </div>

                                        <div class="kes-umrah-flight-line">
                                            <span>{{ __('umrah.route') }}</span>
                                        </div>

                                        <div>
                                            <span>{{ __('umrah.destination') }}</span>
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
                                <span class="kes-v2-section-label">
                                    {{ __('umrah.accommodation') }}
                                </span>

                                <h2>{{ __('umrah.hotel_details') }}</h2>
                            </div>
                        </div>

                        <div class="kes-umrah-detail-hotel-grid">
                            <article>
                                <span>{{ __('umrah.makkah_accommodation') }}</span>

                                <h3>
                                    {{ $package->makkah_hotel
                                        ?: __('umrah.hotel_details_soon') }}
                                </h3>

                                <div>
                                    <p>
                                        <small>{{ __('umrah.nights') }}</small>
                                        <strong>{{ $package->makkah_nights ?: '-' }}</strong>
                                    </p>

                                    <p>
                                        <small>{{ __('umrah.distance') }}</small>
                                        <strong>{{ $package->makkah_distance_display ?: __('umrah.distance_on_request') }}</strong>
                                    </p>

                                    <p>
                                        <small>{{ __('umrah.location') }}</small>
                                        <strong>{{ $package->makkah_location ?: '-' }}</strong>
                                    </p>
                                </div>
                            </article>

                            <article>
                                <span>{{ __('umrah.madinah_accommodation') }}</span>

                                <h3>
                                    {{ $package->madina_hotel
                                        ?: __('umrah.hotel_details_soon') }}
                                </h3>

                                <div>
                                    <p>
                                        <small>{{ __('umrah.nights') }}</small>
                                        <strong>{{ $package->madina_nights ?: '-' }}</strong>
                                    </p>

                                    <p>
                                        <small>{{ __('umrah.distance') }}</small>
                                        <strong>{{ $package->madina_distance_display ?: __('umrah.distance_on_request') }}</strong>
                                    </p>

                                    <p>
                                        <small>{{ __('umrah.location') }}</small>
                                        <strong>{{ $package->madina_location ?: '-' }}</strong>
                                    </p>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="kes-umrah-detail-section">
                        <div class="kes-umrah-detail-section-heading">
                            <div>
                                <span class="kes-v2-section-label">
                                    {{ __('umrah.ground_services') }}
                                </span>

                                <h2>{{ __('umrah.transport_information') }}</h2>
                            </div>
                        </div>

                        <div class="kes-umrah-detail-transport">
                            <span>{{ __('umrah.package_transport') }}</span>
                            <strong>{{ $package->transport ?: $routeText }}</strong>
                        </div>
                    </section>

                    @if (count($services) > 0)
                        <section class="kes-umrah-detail-section">
                            <div class="kes-umrah-detail-section-heading">
                                <div>
                                    <span class="kes-v2-section-label">
                                        {{ __('umrah.package_benefits') }}
                                    </span>

                                    <h2>{{ __('umrah.included_services') }}</h2>
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
                                    <span class="kes-v2-section-label">
                                        {{ __('umrah.complimentary_benefits') }}
                                    </span>

                                    <h2>{{ __('umrah.gifted_services') }}</h2>
                                </div>
                            </div>

                            <div class="kes-umrah-detail-gift-grid">
                                @foreach ($giftedServices as $service)
                                    <div>
                                        <span>{{ __('umrah.gift') }}</span>
                                        <strong>{{ $service }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="kes-umrah-detail-section">
                        <div class="kes-umrah-detail-section-heading">
                            <div>
                                <span class="kes-v2-section-label">
                                    {{ __('umrah.accommodation_prices') }}
                                </span>

                                <h2>{{ __('umrah.price_per_person') }}</h2>
                            </div>
                        </div>

                        <div class="kes-umrah-detail-price-grid">
                            @forelse ($prices as $label => $amount)
                                <article>
                                    <span>{{ $label }}</span>

                                    <strong>
                                        {{ $amount
                                            ? 'PKR '.number_format((float) $amount, 0)
                                            : __('umrah.on_request') }}
                                    </strong>

                                    <small>{{ __('umrah.per_person') }}</small>
                                </article>
                            @empty
                                <article>
                                    <span>{{ __('umrah.package_price') }}</span>
                                    <strong>{{ __('umrah.contact_for_price') }}</strong>
                                    <small>{{ __('umrah.latest_rate_request') }}</small>
                                </article>
                            @endforelse
                        </div>
                    </section>

                </div>

                <aside class="kes-umrah-detail-sidebar">
                    <div class="kes-umrah-booking-card">
                        <span class="kes-v2-section-label">
                            {{ __('umrah.package_booking') }}
                        </span>

                        <h2>{{ __('umrah.reserve_package') }}</h2>

                        <p>{{ __('umrah.booking_guidance') }}</p>

                        <div class="kes-umrah-booking-availability">
                            <div>
                                <span>{{ __('umrah.seats_available') }}</span>
                                <strong>{{ $package->seats_left }}</strong>
                            </div>

                            <div>
                                <span>{{ __('umrah.total_seats') }}</span>
                                <strong>{{ $package->seats_available }}</strong>
                            </div>
                        </div>

                        <a
                            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}?text={{ urlencode($whatsappText) }}"
                            target="_blank"
                            rel="noopener"
                            class="kes-umrah-booking-whatsapp"
                        >
                            {{ __('umrah.book_whatsapp') }}
                        </a>

                        <a href="{{ route('website.contact') }}"
                           class="kes-umrah-booking-contact">
                            {{ __('umrah.send_website_inquiry') }}
                        </a>
                    </div>

                    <div class="kes-umrah-detail-help-card">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="Karwan E Al Saeed">

                        <h3>{{ __('umrah.need_more_information') }}</h3>

                        <p>{{ __('umrah.more_information_text') }}</p>

                        <a href="{{ route('website.umrah') }}">
                            {{ __('umrah.view_all_packages') }}
                        </a>
                    </div>
                </aside>

            </div>
        </section>

    </main>
@endsection