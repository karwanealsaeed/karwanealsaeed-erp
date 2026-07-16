@extends('website.layout')

@section('content')
    <section class="kes-page-header">
        <div class="kes-container">
            <h1>Umrah Packages</h1>
            <p>Latest Umrah groups and packages by Karwan E Al Saeed Travel & Tours.</p>
        </div>
    </section>

    <section class="kes-section">
        <div class="kes-container">
            <div class="umrah-filter-box">
                <form method="GET" action="{{ route('website.umrah') }}">
                    <div class="umrah-filter-grid">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search package / hotel / route"
                        >

                        <select name="airline">
                            <option value="">Filter by Airline</option>
                            @foreach ($airlines as $airline)
                                <option value="{{ $airline }}" @selected(request('airline') === $airline)>
                                    {{ $airline }}
                                </option>
                            @endforeach
                        </select>

                        <select name="route">
                            <option value="">Filter by Route</option>
                            @foreach ($routes as $route)
                                <option value="{{ $route }}" @selected(request('route') === $route)>
                                    {{ $route }}
                                </option>
                            @endforeach
                        </select>

                        <select name="duration">
                            <option value="">Filter by Duration</option>
                            <option value="1-10" @selected(request('duration') === '1-10')>1 - 10 Nights</option>
                            <option value="11-20" @selected(request('duration') === '11-20')>11 - 20 Nights</option>
                            <option value="21-30" @selected(request('duration') === '21-30')>21 - 30 Nights</option>
                            <option value="31+" @selected(request('duration') === '31+')>31+ Nights</option>
                        </select>

                        <select name="sort">
                            <option value="">Sort By</option>
                            <option value="latest" @selected(request('sort') === 'latest')>Latest</option>
                            <option value="price_low" @selected(request('sort') === 'price_low')>Price Low to High</option>
                            <option value="price_high" @selected(request('sort') === 'price_high')>Price High to Low</option>
                            <option value="duration_low" @selected(request('sort') === 'duration_low')>Duration Low to High</option>
                            <option value="duration_high" @selected(request('sort') === 'duration_high')>Duration High to Low</option>
                        </select>
                    </div>

                    <div style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
                        <button type="submit" class="kes-btn kes-btn-primary">
                            Apply Filters
                        </button>

                        <a href="{{ route('website.umrah') }}" class="kes-btn kes-btn-outline">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="umrah-count">
                Showing {{ $packages->count() }} package(s)
            </div>

            @if ($packages->count() > 0)
                <div class="umrah-compact-grid">
                    @foreach ($packages as $package)
                        @php
                            $group = $package->umrahGroupFlight;
                            $segments = is_array($group?->flight_segments) ? array_values($group->flight_segments) : [];

                            $firstSegment = $segments[0] ?? [];
                            $lastSegment = $segments[count($segments) - 1] ?? [];

                            $routeText = $package->flight_route ?: ($group?->route ?: '-');
                            $routeParts = collect(explode('-', str_replace(' ', '', $routeText)))->filter()->values();

                            $fromCode = $routeParts[0] ?? ($firstSegment['from'] ?? 'MUX');
                            $toCode = $routeParts[1] ?? ($firstSegment['to'] ?? 'JED');
                            $backCode = $routeParts[2] ?? ($lastSegment['to'] ?? $fromCode);

                            $departureDate = $group?->departure_date ? $group->departure_date->format('d M Y') : '-';
                            $returnDate = $group?->return_date ? $group->return_date->format('d M Y') : '-';
                            $departureShort = $group?->departure_date ? $group->departure_date->format('d M') : '-';
                            $returnShort = $group?->return_date ? $group->return_date->format('d M') : '-';

                            $departureTime = $firstSegment['departure_time'] ?? '-';
                            $returnTime = $lastSegment['departure_time'] ?? '-';

                            $baggage = $group?->baggage ?: '-';
                            $meals = $group?->meals === 'YES' ? 'Meals Yes' : 'Meals No';

                            $whatsappText = 'Assalam o Alaikum, mujhe Umrah Package ' . $package->package_no . ' - ' . $package->title . ' ke baare me details chahiye.';
                        @endphp

                        <div class="umrah-compact-card">
                            <div class="umrah-card-head">
                                <div class="umrah-airline">
                                    <div class="umrah-airline-name">{{ $package->airline ?: 'Umrah Package' }}</div>
                                    <div class="umrah-code">{{ $package->package_no }} | {{ $package->group_code ?: '-' }}</div>
                                </div>

                                @if ($package->package_image)
                                    <img class="umrah-mini-logo" src="{{ asset('storage/' . $package->package_image) }}" alt="{{ $package->title }}">
                                @elseif (file_exists(public_path('images/logo.png')))
                                    <img class="umrah-mini-logo" src="{{ asset('images/logo.png') }}" alt="Karwan E Al Saeed">
                                @endif
                            </div>

                            <div class="umrah-route-row">
                                <div class="umrah-date-box">
                                    <div class="umrah-date-label">Departure</div>
                                    <div class="umrah-date-value">{{ $departureDate }}</div>
                                </div>

                                <div class="umrah-days-pill">
                                    ✈ {{ $package->duration_nights ?: '-' }} Nights
                                </div>

                                <div class="umrah-date-box">
                                    <div class="umrah-date-label">Return</div>
                                    <div class="umrah-date-value">{{ $returnDate }}</div>
                                </div>
                            </div>

                            <div class="umrah-card-body">
                                <div class="umrah-flight-line">
                                    <strong>{{ $fromCode }}</strong>
                                    <span>🛫</span>
                                    <strong>{{ $toCode }}</strong>
                                    <span class="umrah-muted">📅 {{ $departureShort }}</span>
                                    <span class="umrah-muted">🕒 {{ $departureTime }}</span>
                                </div>

                                <div class="umrah-flight-line">
                                    <strong>{{ $toCode }}</strong>
                                    <span>🛫</span>
                                    <strong>{{ $backCode }}</strong>
                                    <span class="umrah-muted">📅 {{ $returnShort }}</span>
                                    <span class="umrah-muted">🕒 {{ $returnTime }}</span>
                                </div>

                                <div class="umrah-flight-line">
                                    <span class="umrah-muted">🧳 {{ $baggage }}</span>
                                    <span class="umrah-muted">🍽 {{ $meals }}</span>
                                </div>

                                <div class="umrah-hotels">
                                    <div class="umrah-hotel-line">
                                        <div class="umrah-hotel-icon">🕋</div>
                                        <div>
                                            <div class="umrah-hotel-name">{{ $package->makkah_hotel ?: 'Makkah Hotel' }}</div>
                                            <div class="umrah-hotel-distance">📍 {{ $package->makkah_distance ?: '-' }}</div>
                                        </div>
                                        <div class="umrah-night">{{ $package->makkah_nights ?: '-' }}N</div>
                                    </div>

                                    <div class="umrah-hotel-line">
                                        <div class="umrah-hotel-icon">🕌</div>
                                        <div>
                                            <div class="umrah-hotel-name">{{ $package->madina_hotel ?: 'Madinah Hotel' }}</div>
                                            <div class="umrah-hotel-distance">📍 {{ $package->madina_distance ?: '-' }}</div>
                                        </div>
                                        <div class="umrah-night">{{ $package->madina_nights ?: '-' }}N</div>
                                    </div>
                                </div>

                                <div class="umrah-transport">
                                    🚌 {{ $package->transport ?: $routeText }}
                                </div>

                                <div class="umrah-prices">
                                    <div class="umrah-price">
                                        <div class="umrah-price-label">Sharing</div>
                                        <div class="umrah-price-value">{{ $package->sharing_price ? number_format((float) $package->sharing_price, 0) : 'N/A' }}</div>
                                    </div>

                                    <div class="umrah-price">
                                        <div class="umrah-price-label">Quad</div>
                                        <div class="umrah-price-value">{{ $package->quad_price ? number_format((float) $package->quad_price, 0) : 'N/A' }}</div>
                                    </div>

                                    <div class="umrah-price">
                                        <div class="umrah-price-label">Triple</div>
                                        <div class="umrah-price-value">{{ $package->triple_price ? number_format((float) $package->triple_price, 0) : 'N/A' }}</div>
                                    </div>

                                    <div class="umrah-price">
                                        <div class="umrah-price-label">Double</div>
                                        <div class="umrah-price-value">{{ $package->double_price ? number_format((float) $package->double_price, 0) : 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="umrah-card-foot">
                                <div class="umrah-seat">
                                    ● {{ $package->seats_left }} / {{ $package->seats_available }} seats
                                </div>

                                <div class="umrah-actions">
                                    <a class="kes-btn kes-btn-primary" href="{{ route('website.umrah.show', $package) }}">
                                        Details
                                    </a>

                                    <a class="kes-btn kes-btn-whatsapp"
                                       target="_blank"
                                       href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}?text={{ urlencode($whatsappText) }}">
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="kes-content-box">
                    <h2 style="color:#062f20; margin-top:0;">No package found</h2>
                    <p>Is filter ke mutabiq koi Umrah package nahi mila.</p>
                </div>
            @endif
        </div>
    </section>
@endsection