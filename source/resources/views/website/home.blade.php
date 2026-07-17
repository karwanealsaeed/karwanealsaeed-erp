@extends('website.layout')

@section('content')
    <main class="kes-home-v2">

        <section class="kes-v2-hero">
            <div class="kes-v2-hero-overlay"></div>

            <div class="kes-v2-hero-inner">
                <div class="kes-v2-hero-content">
                    <div class="kes-v2-eyebrow">
                        <span></span>
                        Premium Travel & Umrah Services
                    </div>

                    <h1>
                        Your Trusted Partner for
                        <strong>Umrah, Visas, Tickets & Tours</strong>
                    </h1>

                    <p class="kes-v2-hero-text">
                        Professional travel solutions with clear guidance, reliable service
                        and personal support throughout your journey.
                    </p>

                    <p class="kes-v2-hero-urdu" lang="ur" dir="rtl">
                        عمرہ، ویزہ، ائیر ٹکٹ، ہوٹل بکنگ اور پاکستان ٹورز کے لیے
                        قابلِ اعتماد اور پیشہ ورانہ سفری خدمات۔
                    </p>

                    <div class="kes-v2-hero-actions">
                        <a href="{{ route('website.umrah') }}" class="kes-v2-btn kes-v2-btn-gold">
                            Explore Umrah Packages
                        </a>

                        <a href="{{ route('website.contact') }}" class="kes-v2-btn kes-v2-btn-light">
                            Contact Our Team
                        </a>
                    </div>

                    <div class="kes-v2-trust-row">
                        <div>
                            <strong>Professional</strong>
                            <span>Travel Guidance</span>
                        </div>

                        <div>
                            <strong>Reliable</strong>
                            <span>Customer Support</span>
                        </div>

                        <div>
                            <strong>Complete</strong>
                            <span>Travel Solutions</span>
                        </div>
                    </div>
                </div>

                <aside class="kes-v2-hero-panel">
                    <div class="kes-v2-panel-logo">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="Karwan E Al Saeed Travel & Tours">
                    </div>

                    <span class="kes-v2-panel-label">Our Core Services</span>

                    <h2>Plan Your Journey With Confidence</h2>

                    <div class="kes-v2-panel-services">
                        <a href="{{ route('website.umrah') }}">
                            <span>01</span>
                            <div>
                                <strong>Umrah Packages</strong>
                                <small>Hotels, transport and complete support</small>
                            </div>
                        </a>

                        <a href="{{ route('website.visas') }}">
                            <span>02</span>
                            <div>
                                <strong>Visit Visas</strong>
                                <small>Application and documentation guidance</small>
                            </div>
                        </a>

                        <a href="{{ route('website.tickets') }}">
                            <span>03</span>
                            <div>
                                <strong>Air Ticketing</strong>
                                <small>Domestic and international booking</small>
                            </div>
                        </a>

                        <a href="{{ route('website.hotels') }}">
                            <span>04</span>
                            <div>
                                <strong>Hotel Booking</strong>
                                <small>Stay options for every journey</small>
                            </div>
                        </a>
                    </div>

                    <a href="{{ route('website.services') }}" class="kes-v2-panel-link">
                        View All Services
                        <span>&rarr;</span>
                    </a>
                </aside>
            </div>
        </section>

        <section class="kes-v2-intro">
            <div class="kes-v2-container kes-v2-intro-grid">
                <div>
                    <span class="kes-v2-section-label">Welcome to Karwan E Al Saeed</span>
                    <h2>Travel Services Built Around Trust and Care</h2>
                </div>

                <div>
                    <p>
                        We provide complete assistance for Umrah, visas, air tickets,
                        hotel reservations, travel insurance and Pakistan tours.
                    </p>

                    <a href="{{ route('website.about') }}" class="kes-v2-text-link">
                        Learn More About Us
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="kes-v2-services">
            <div class="kes-v2-container">
                <div class="kes-v2-section-heading">
                    <div>
                        <span class="kes-v2-section-label">What We Offer</span>
                        <h2>Complete Travel Solutions</h2>
                    </div>

                    <p>
                        Everything you need for a smooth, secure and professionally managed journey.
                    </p>
                </div>

                <div class="kes-v2-service-grid">
                    <a href="{{ route('website.umrah') }}" class="kes-v2-service-card featured">
                        <span class="kes-v2-service-number">01</span>
                        <div class="kes-v2-service-symbol">UM</div>
                        <h3>Umrah Packages</h3>
                        <p>
                            Reliable Umrah packages with suitable hotels, transportation
                            and complete travel guidance.
                        </p>
                        <span class="kes-v2-card-link">Explore Packages &rarr;</span>
                    </a>

                    <a href="{{ route('website.visas') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">02</span>
                        <div class="kes-v2-service-symbol">VS</div>
                        <h3>Visit Visas</h3>
                        <p>
                            Professional guidance for visa requirements, documents
                            and application processing.
                        </p>
                        <span class="kes-v2-card-link">Visa Services &rarr;</span>
                    </a>

                    <a href="{{ route('website.tickets') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">03</span>
                        <div class="kes-v2-service-symbol">AT</div>
                        <h3>Air Ticketing</h3>
                        <p>
                            Domestic and international flight reservations with
                            suitable travel options.
                        </p>
                        <span class="kes-v2-card-link">Book Tickets &rarr;</span>
                    </a>

                    <a href="{{ route('website.hotels') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">04</span>
                        <div class="kes-v2-service-symbol">HB</div>
                        <h3>Hotel Booking</h3>
                        <p>
                            Hotel arrangements for Umrah, family holidays,
                            business travel and group tours.
                        </p>
                        <span class="kes-v2-card-link">Find Hotels &rarr;</span>
                    </a>

                    <a href="{{ route('website.insurance') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">05</span>
                        <div class="kes-v2-service-symbol">TI</div>
                        <h3>Travel Insurance</h3>
                        <p>
                            Insurance guidance according to your destination,
                            travel plan and requirements.
                        </p>
                        <span class="kes-v2-card-link">Get Guidance &rarr;</span>
                    </a>

                    <a href="{{ route('website.tours') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">06</span>
                        <div class="kes-v2-service-symbol">PT</div>
                        <h3>Pakistan Tours</h3>
                        <p>
                            Professionally planned family, group and corporate
                            tours across beautiful Pakistan.
                        </p>
                        <span class="kes-v2-card-link">Explore Pakistan &rarr;</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="kes-v2-why">
            <div class="kes-v2-container kes-v2-why-grid">
                <div class="kes-v2-why-card">
                    <span class="kes-v2-section-label light">Why Choose Us</span>

                    <h2>A Trusted Name for Your Travel Needs</h2>

                    <p>
                        Our team focuses on professional service, transparent communication
                        and dependable assistance from inquiry to completion.
                    </p>

                    <a href="{{ route('website.contact') }}" class="kes-v2-btn kes-v2-btn-gold">
                        Start Your Inquiry
                    </a>
                </div>

                <div class="kes-v2-benefit-grid">
                    <article>
                        <span>01</span>
                        <h3>Clear Guidance</h3>
                        <p>Easy-to-understand information throughout your booking process.</p>
                    </article>

                    <article>
                        <span>02</span>
                        <h3>Customer Support</h3>
                        <p>Professional assistance before, during and after your journey.</p>
                    </article>

                    <article>
                        <span>03</span>
                        <h3>Reliable Planning</h3>
                        <p>Carefully managed travel arrangements according to your needs.</p>
                    </article>

                    <article>
                        <span>04</span>
                        <h3>Multiple Services</h3>
                        <p>Umrah, visas, tickets, hotels and tours under one trusted name.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="kes-v2-process">
            <div class="kes-v2-container">
                <div class="kes-v2-section-heading centered">
                    <div>
                        <span class="kes-v2-section-label">Simple Process</span>
                        <h2>Start Your Journey in Three Steps</h2>
                    </div>
                </div>

                <div class="kes-v2-process-grid">
                    <article>
                        <span>01</span>
                        <h3>Send Your Inquiry</h3>
                        <p>Contact us through phone, WhatsApp or the website support form.</p>
                    </article>

                    <article>
                        <span>02</span>
                        <h3>Get Professional Guidance</h3>
                        <p>Our team will explain the available options and requirements.</p>
                    </article>

                    <article>
                        <span>03</span>
                        <h3>Confirm Your Booking</h3>
                        <p>Complete your documents and proceed with your selected service.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="kes-v2-cta">
            <div class="kes-v2-container">
                <div class="kes-v2-cta-inner">
                    <div>
                        <span>Ready to plan your next journey?</span>
                        <h2>Speak With Our Travel Team Today</h2>
                    </div>

                    <div class="kes-v2-cta-actions">
                        <a href="{{ route('website.contact') }}" class="kes-v2-btn kes-v2-btn-gold">
                            Contact Us
                        </a>

                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                           target="_blank"
                           rel="noopener"
                           class="kes-v2-btn kes-v2-btn-outline-white">
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>
@endsection