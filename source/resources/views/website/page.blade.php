@extends('website.layout')

@section('content')
    <main class="kes-public-page-v2">

        <section class="kes-page-v2-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-page-v2-hero-inner">
                <div>
                    <nav class="kes-page-v2-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route('website.home') }}">Home</a>
                        <span>/</span>
                        <span>{{ $page['title'] }}</span>
                    </nav>

                    <span class="kes-page-v2-label">
                        Karwan E Al Saeed Travel & Tours
                    </span>

                    <h1>{{ $page['heading'] }}</h1>

                    <p>{{ $page['description'] }}</p>
                </div>

                <div class="kes-page-v2-logo">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Karwan E Al Saeed Travel & Tours">
                </div>
            </div>
        </section>

        <section class="kes-page-v2-body">
            <div class="kes-v2-container kes-page-v2-grid">

                <article class="kes-page-v2-content">
                    <span class="kes-v2-section-label">
                        {{ $page['title'] }}
                    </span>

                    <h2>{{ $page['heading'] }}</h2>

                    <div class="kes-page-v2-description">
                        <p>{{ $page['content'] }}</p>
                    </div>

                    <div class="kes-page-v2-urdu" lang="ur" dir="rtl">
                        <span>اردو معلومات</span>
                        <p>{{ $page['urdu'] }}</p>
                    </div>

                    @if (($page['title'] ?? '') === 'Our Services')
                        <div class="kes-page-v2-services-grid">
                            <a href="{{ route('website.umrah') }}">
                                <span>01</span>
                                <strong>Umrah Packages</strong>
                                <small>Complete Umrah travel assistance</small>
                            </a>

                            <a href="{{ route('website.visas') }}">
                                <span>02</span>
                                <strong>Visit Visas</strong>
                                <small>Visa documentation and guidance</small>
                            </a>

                            <a href="{{ route('website.tickets') }}">
                                <span>03</span>
                                <strong>Air Ticketing</strong>
                                <small>Domestic and international flights</small>
                            </a>

                            <a href="{{ route('website.hotels') }}">
                                <span>04</span>
                                <strong>Hotel Booking</strong>
                                <small>Suitable hotels for every journey</small>
                            </a>

                            <a href="{{ route('website.insurance') }}">
                                <span>05</span>
                                <strong>Travel Insurance</strong>
                                <small>Travel protection guidance</small>
                            </a>

                            <a href="{{ route('website.tours') }}">
                                <span>06</span>
                                <strong>Pakistan Tours</strong>
                                <small>Family, group and corporate tours</small>
                            </a>
                        </div>
                    @endif

                    @if (($page['title'] ?? '') === 'Contact Us')
                        <div class="kes-page-v2-contact-grid">
                            <div>
                                <span>Phone</span>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->mobile ?? '+923012592342') }}">
                                    {{ $company->mobile ?? '+92 301 2592342' }}
                                </a>
                            </div>

                            <div>
                                <span>WhatsApp</span>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                                   target="_blank"
                                   rel="noopener">
                                    {{ $company->whatsapp ?? '+92 301 2592342' }}
                                </a>
                            </div>

                            <div>
                                <span>Email</span>
                                <a href="mailto:{{ $company->email ?? 'info@karwanealsaeed.com' }}">
                                    {{ $company->email ?? 'info@karwanealsaeed.com' }}
                                </a>
                            </div>

                            <div>
                                <span>Office Address</span>
                                <p>
                                    {{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}
                                </p>
                            </div>
                        </div>

                        <div class="kes-page-v2-map">
                            <iframe
                                src="https://www.google.com/maps?q={{ urlencode($company->address ?? 'College Road Chichawatni Pakistan') }}&output=embed"
                                width="100%"
                                height="390"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Karwan E Al Saeed Office Location">
                            </iframe>
                        </div>
                    @else
                        <div class="kes-page-v2-cta-box">
                            <div>
                                <span>Need professional guidance?</span>
                                <h3>Speak With Our Travel Team</h3>
                                <p>
                                    Contact us for requirements, documents, availability
                                    and complete service information.
                                </p>
                            </div>

                            <a href="{{ route('website.contact') }}" class="kes-v2-btn kes-v2-btn-gold">
                                Send an Inquiry
                            </a>
                        </div>
                    @endif
                </article>

                <aside class="kes-page-v2-sidebar">
                    <div class="kes-page-v2-side-card">
                        <span class="kes-v2-section-label">Quick Services</span>
                        <h3>Explore Our Services</h3>

                        <nav>
                            <a href="{{ route('website.umrah') }}">
                                Umrah Packages <span>&rarr;</span>
                            </a>

                            <a href="{{ route('website.visas') }}">
                                Visit Visas <span>&rarr;</span>
                            </a>

                            <a href="{{ route('website.tickets') }}">
                                Air Ticketing <span>&rarr;</span>
                            </a>

                            <a href="{{ route('website.hotels') }}">
                                Hotel Booking <span>&rarr;</span>
                            </a>

                            <a href="{{ route('website.tours') }}">
                                Pakistan Tours <span>&rarr;</span>
                            </a>
                        </nav>
                    </div>

                    <div class="kes-page-v2-help-card">
                        <span>Customer Support</span>
                        <h3>How Can We Help?</h3>

                        <p>
                            Our team is available to guide you about travel services,
                            bookings and documentation.
                        </p>

                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                           target="_blank"
                           rel="noopener">
                            Contact on WhatsApp
                        </a>
                    </div>
                </aside>

            </div>
        </section>

    </main>
@endsection