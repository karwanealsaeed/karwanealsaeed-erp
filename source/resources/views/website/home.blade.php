@extends('website.layout')

@section('content')
    <section class="kes-hero">
        <div class="kes-hero-grid">
            <div>
                <div class="kes-hero-kicker">
                    ✨ Premium Travel & Umrah Services
                </div>

                <h1>
                    Your Trusted Travel Partner for Umrah, Visas, Air Tickets & Tours
                </h1>

                <p>
                    Karwan E Al Saeed Travel & Tours provides reliable and professional travel services
                    for Umrah packages, visit visas, air tickets, hotel booking, travel insurance and Pakistan tours.
                </p>

                <div class="kes-hero-actions">
                    <a href="{{ route('website.umrah') }}" class="kes-btn kes-btn-primary">
                        View Umrah Packages
                    </a>

                    <a href="{{ route('website.contact') }}" class="kes-btn kes-btn-outline">
                        Contact Us
                    </a>
                </div>
            </div>

            <div class="kes-hero-card">
                <h2>Premium Travel Services</h2>

                <p>
                    عمرہ، ویزہ، ائیر ٹکٹ، ہوٹل بکنگ اور پاکستان ٹورز کے لیے قابل اعتماد سروس۔
                </p>

                <div class="kes-service-list">
                    <div class="kes-service-item">
                        <span class="kes-service-check">✓</span>
                        <span>Umrah Packages</span>
                    </div>

                    <div class="kes-service-item">
                        <span class="kes-service-check">✓</span>
                        <span>Visit Visas</span>
                    </div>

                    <div class="kes-service-item">
                        <span class="kes-service-check">✓</span>
                        <span>Air Ticketing</span>
                    </div>

                    <div class="kes-service-item">
                        <span class="kes-service-check">✓</span>
                        <span>Hotel Booking</span>
                    </div>

                    <div class="kes-service-item">
                        <span class="kes-service-check">✓</span>
                        <span>Pakistan Tours</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="kes-section">
        <div class="kes-container">
            <div class="kes-section-title">
                <h2>Our Services</h2>
                <p>Complete travel solutions under one trusted name.</p>
            </div>

            <div class="kes-card-grid">
                <a href="{{ route('website.umrah') }}" class="kes-card">
                    <div class="kes-card-icon">☪</div>
                    <h3>Umrah Packages</h3>
                    <p>Reliable Umrah packages with hotels, transport and complete travel guidance.</p>
                </a>

                <a href="{{ route('website.visas') }}" class="kes-card">
                    <div class="kes-card-icon">🛂</div>
                    <h3>Visit Visas</h3>
                    <p>Visa guidance and application support for different countries.</p>
                </a>

                <a href="{{ route('website.tickets') }}" class="kes-card">
                    <div class="kes-card-icon">✈</div>
                    <h3>Air Ticketing</h3>
                    <p>Domestic and international air ticket booking support.</p>
                </a>

                <a href="{{ route('website.hotels') }}" class="kes-card">
                    <div class="kes-card-icon">🏨</div>
                    <h3>Hotel Booking</h3>
                    <p>Hotel booking for Umrah, family travel and business trips.</p>
                </a>

                <a href="{{ route('website.insurance') }}" class="kes-card">
                    <div class="kes-card-icon">🛡</div>
                    <h3>Travel Insurance</h3>
                    <p>Travel insurance guidance according to your travel needs.</p>
                </a>

                <a href="{{ route('website.tours') }}" class="kes-card">
                    <div class="kes-card-icon">🏔</div>
                    <h3>Pakistan Tours</h3>
                    <p>Family and group tours across beautiful Pakistan.</p>
                </a>
            </div>
        </div>
    </section>
@endsection