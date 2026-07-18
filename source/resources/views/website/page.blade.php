@extends('website.layout')

@section('content')
    <main class="kes-public-page-v2">

        <section class="kes-page-v2-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-page-v2-hero-inner">
                <div>
                    <nav class="kes-page-v2-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route('website.home') }}">
                            {{ __('website.home') }}
                        </a>

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

                    <div class="kes-page-v2-urdu"
                         lang="{{ app()->isLocale('ur') ? 'en' : 'ur' }}"
                         dir="{{ app()->isLocale('ur') ? 'ltr' : 'rtl' }}">

                        <span>{{ __('website.secondary_information') }}</span>
                        <p>{{ $page['urdu'] }}</p>
                    </div>

                    @if (($slug ?? '') === 'our-services')
                        <div class="kes-page-v2-services-grid">
                            <a href="{{ route('website.umrah') }}">
                                <span>01</span>
                                <strong>{{ __('website.umrah_packages') }}</strong>
                                <small>{{ __('website.complete_umrah_assistance') }}</small>
                            </a>

                            <a href="{{ route('website.visas') }}">
                                <span>02</span>
                                <strong>{{ __('website.visit_visas') }}</strong>
                                <small>{{ __('website.visa_documentation_guidance') }}</small>
                            </a>

                            <a href="{{ route('website.tickets') }}">
                                <span>03</span>
                                <strong>{{ __('website.air_ticketing') }}</strong>
                                <small>{{ __('website.domestic_international_flights') }}</small>
                            </a>

                            <a href="{{ route('website.hotels') }}">
                                <span>04</span>
                                <strong>{{ __('website.hotel_booking') }}</strong>
                                <small>{{ __('website.suitable_hotels') }}</small>
                            </a>

                            <a href="{{ route('website.insurance') }}">
                                <span>05</span>
                                <strong>{{ __('website.travel_insurance') }}</strong>
                                <small>{{ __('website.travel_protection_guidance') }}</small>
                            </a>

                            <a href="{{ route('website.tours') }}">
                                <span>06</span>
                                <strong>{{ __('website.pakistan_tours') }}</strong>
                                <small>{{ __('website.family_group_tours') }}</small>
                            </a>
                        </div>
                    @endif

                    @if (($slug ?? '') === 'contact')
                        <div class="kes-page-v2-contact-grid">
                            <div>
                                <span>{{ __('website.phone') }}</span>

                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->mobile ?? '+923012592342') }}">
                                    {{ $company->mobile ?? '+92 301 2592342' }}
                                </a>
                            </div>

                            <div>
                                <span>{{ __('website.whatsapp') }}</span>

                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                                   target="_blank"
                                   rel="noopener">
                                    {{ $company->whatsapp ?? '+92 301 2592342' }}
                                </a>
                            </div>

                            <div>
                                <span>{{ __('website.email') }}</span>

                                <a href="mailto:{{ $company->email ?? 'info@karwanealsaeed.com' }}">
                                    {{ $company->email ?? 'info@karwanealsaeed.com' }}
                                </a>
                            </div>

                            <div>
                                <span>{{ __('website.office_address') }}</span>

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
                                title="{{ __('website.office_location') }}">
                            </iframe>
                        </div>
                    @else
                        <div class="kes-page-v2-cta-box">
                            <div>
                                <span>{{ __('website.need_guidance') }}</span>

                                <h3>{{ __('website.speak_travel_team') }}</h3>

                                <p>{{ __('website.inquiry_help_text') }}</p>
                            </div>

                            <a href="{{ route('website.contact') }}"
                               class="kes-v2-btn kes-v2-btn-gold">
                                {{ __('website.send_inquiry') }}
                            </a>
                        </div>
                    @endif
                </article>

                <aside class="kes-page-v2-sidebar">
                    <div class="kes-page-v2-side-card">
                        <span class="kes-v2-section-label">
                            {{ __('website.quick_services') }}
                        </span>

                        <h3>{{ __('website.explore_services') }}</h3>

                        <nav>
                            <a href="{{ route('website.umrah') }}">
                                {{ __('website.umrah_packages') }}
                                <span>{{ app()->isLocale('ur') ? '←' : '→' }}</span>
                            </a>

                            <a href="{{ route('website.visas') }}">
                                {{ __('website.visit_visas') }}
                                <span>{{ app()->isLocale('ur') ? '←' : '→' }}</span>
                            </a>

                            <a href="{{ route('website.tickets') }}">
                                {{ __('website.air_ticketing') }}
                                <span>{{ app()->isLocale('ur') ? '←' : '→' }}</span>
                            </a>

                            <a href="{{ route('website.hotels') }}">
                                {{ __('website.hotel_booking') }}
                                <span>{{ app()->isLocale('ur') ? '←' : '→' }}</span>
                            </a>

                            <a href="{{ route('website.tours') }}">
                                {{ __('website.pakistan_tours') }}
                                <span>{{ app()->isLocale('ur') ? '←' : '→' }}</span>
                            </a>
                        </nav>
                    </div>

                    <div class="kes-page-v2-help-card">
                        <span>{{ __('website.customer_support') }}</span>

                        <h3>{{ __('website.how_can_help') }}</h3>

                        <p>{{ __('website.team_help_text') }}</p>

                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                           target="_blank"
                           rel="noopener">
                            {{ __('website.contact_whatsapp') }}
                        </a>
                    </div>
                </aside>

            </div>
        </section>

    </main>
@endsection