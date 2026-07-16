@extends('website.layout')

@section('content')
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

        $services = $package->included_services ?: [];

        if ($package->other_included_service) {
            $services[] = $package->other_included_service;
        }

        $giftedServices = $package->gifted_services ?: [];

        $whatsappText = 'Assalam o Alaikum, mujhe Umrah Package ' . $package->package_no . ' - ' . $package->title . ' book karna hai.';
    @endphp

    <section class="kes-page-header">
        <div class="kes-container">
            <h1>{{ $package->title }}</h1>
            <p>{{ $package->subtitle ?: 'Complete Umrah package details' }}</p>
        </div>
    </section>

    <section class="kes-section">
        <div class="kes-container">
            <div class="umrah-detail-card">
                <div class="umrah-detail-head">
                    <h2 class="umrah-detail-title">{{ $package->airline ?: 'Umrah Package' }}</h2>
                    <div class="umrah-detail-sub">
                        {{ $package->package_no }} | {{ $package->group_code ?: '-' }} | {{ $routeText }}
                    </div>
                </div>

                <div class="umrah-detail-body">
                    @if ($package->description)
                        <p style="line-height:1.8; margin-top:0;">{{ $package->description }}</p>
                    @endif

                    <div class="umrah-detail-grid">
                        <div class="umrah-detail-box">
                            <div class="umrah-detail-label">Departure</div>
                            <div class="umrah-detail-value">{{ $group?->departure_date ? $group->departure_date->format('d M Y') : '-' }}</div>
                        </div>

                        <div class="umrah-detail-box">
                            <div class="umrah-detail-label">Return</div>
                            <div class="umrah-detail-value">{{ $group?->return_date ? $group->return_date->format('d M Y') : '-' }}</div>
                        </div>

                        <div class="umrah-detail-box">
                            <div class="umrah-detail-label">Duration</div>
                            <div class="umrah-detail-value">{{ $package->duration_nights ?: '-' }} Night(s)</div>
                        </div>

                        <div class="umrah-detail-box">
                            <div class="umrah-detail-label">Availability</div>
                            <div class="umrah-detail-value">{{ $package->seats_left }} / {{ $package->seats_available }} seats</div>
                        </div>
                    </div>

                    <div class="umrah-detail-section">
                        <h3>Flight Details</h3>

                        <div class="umrah-detail-grid">
                            @forelse ($segments as $segment)
                                <div class="umrah-detail-box">
                                    <div class="umrah-detail-label">{{ $segment['flight_no'] ?? 'Flight' }}</div>
                                    <div class="umrah-detail-value">
                                        {{ $segment['from'] ?? '-' }} → {{ $segment['to'] ?? '-' }}
                                    </div>
                                    <div style="margin-top:6px; color:#66756d; font-weight:800;">
                                        {{ filled($segment['departure_date'] ?? null) ? \Carbon\Carbon::parse($segment['departure_date'])->format('d M Y') : '-' }}
                                        {{ $segment['departure_time'] ?? '' }}
                                    </div>
                                </div>
                            @empty
                                <div class="umrah-detail-box">
                                    <div class="umrah-detail-label">Route</div>
                                    <div class="umrah-detail-value">{{ $fromCode }} → {{ $toCode }} → {{ $backCode }}</div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="umrah-detail-section">
                        <h3>Hotel Details</h3>

                        <div class="umrah-detail-grid">
                            <div class="umrah-detail-box">
                                <div class="umrah-detail-label">Makkah Hotel</div>
                                <div class="umrah-detail-value">{{ $package->makkah_hotel ?: '-' }}</div>
                                <div style="margin-top:6px; color:#66756d; font-weight:800;">
                                    {{ $package->makkah_nights ?: '-' }} Night(s) |
                                    {{ $package->makkah_distance ?: '-' }} |
                                    {{ $package->makkah_location ?: '-' }}
                                </div>
                            </div>

                            <div class="umrah-detail-box">
                                <div class="umrah-detail-label">Madinah Hotel</div>
                                <div class="umrah-detail-value">{{ $package->madina_hotel ?: '-' }}</div>
                                <div style="margin-top:6px; color:#66756d; font-weight:800;">
                                    {{ $package->madina_nights ?: '-' }} Night(s) |
                                    {{ $package->madina_distance ?: '-' }} |
                                    {{ $package->madina_location ?: '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="umrah-detail-section">
                        <h3>Transport</h3>
                        <div class="umrah-detail-box">
                            <div class="umrah-detail-value">{{ $package->transport ?: '-' }}</div>
                        </div>
                    </div>

                    @if (count($services) > 0)
                        <div class="umrah-detail-section">
                            <h3>Included Services</h3>
                            <div class="umrah-service-pills">
                                @foreach ($services as $service)
                                    <span class="umrah-service-pill">✓ {{ $service }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (count($giftedServices) > 0)
                        <div class="umrah-detail-section">
                            <h3>Complimentary Gifted Services</h3>
                            <div class="umrah-service-pills">
                                @foreach ($giftedServices as $service)
                                    <span class="umrah-service-pill umrah-gift-pill">🎁 {{ $service }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="umrah-detail-section">
                        <h3>Price Per Person</h3>

                        <div class="umrah-detail-grid">
                            @foreach ($package->prices_list as $label => $amount)
                                <div class="umrah-detail-box">
                                    <div class="umrah-detail-label">{{ $label }}</div>
                                    <div class="umrah-detail-value">
                                        {{ $amount ? 'PKR ' . number_format((float) $amount, 0) : 'Not Available' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:28px;">
                        <a class="kes-btn kes-btn-primary"
                           target="_blank"
                           href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}?text={{ urlencode($whatsappText) }}">
                            Book / Ask on WhatsApp
                        </a>

                        <a class="kes-btn kes-btn-outline" href="{{ route('website.umrah') }}">
                            Back to Packages
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection