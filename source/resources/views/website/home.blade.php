@extends('website.layout')

@section('content')
    <main class="kes-home-v2">

        <section class="kes-v2-hero">
            <div class="kes-v2-hero-overlay"></div>

            <div class="kes-v2-hero-inner">
                <div class="kes-v2-hero-content">
                    <div class="kes-v2-eyebrow">
                        <span></span>
                        {{ __('home.eyebrow') }}
                    </div>

                    <h1>
                        {{ __('home.hero_title_prefix') }}
                        <strong>{{ __('home.hero_title_highlight') }}</strong>
                    </h1>

                    <p class="kes-v2-hero-text">
                        {{ __('home.hero_text') }}
                    </p>

                    <p class="kes-v2-hero-urdu" lang="ur" dir="rtl">
                        {{ __('home.hero_secondary_text') }}
                    </p>

                    <div class="kes-v2-hero-actions">
                        <a href="{{ route('website.umrah') }}" class="kes-v2-btn kes-v2-btn-gold">
                            {{ __('home.explore_packages') }}
                        </a>

                        <a href="{{ route('website.contact') }}" class="kes-v2-btn kes-v2-btn-light">
                            {{ __('home.contact_team') }}
                        </a>
                    </div>

                    <div class="kes-v2-trust-row">
                        <div>
                            <strong>{{ __('home.professional') }}</strong>
                            <span>{{ __('home.travel_guidance') }}</span>
                        </div>

                        <div>
                            <strong>{{ __('home.reliable') }}</strong>
                            <span>{{ __('home.customer_support') }}</span>
                        </div>

                        <div>
                            <strong>{{ __('home.complete') }}</strong>
                            <span>{{ __('home.travel_solutions') }}</span>
                        </div>
                    </div>
                </div>

                <aside class="kes-v2-hero-panel">
                    <div class="kes-v2-panel-logo">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="Karwan E Al Saeed Travel & Tours">
                    </div>

                    <span class="kes-v2-panel-label">{{ __('home.core_services') }}</span>

                    <h2>{{ __('home.plan_confidence') }}</h2>

                    <div class="kes-v2-panel-services">
                        <a href="{{ route('website.umrah') }}">
                            <span>01</span>
                            <div>
                                <strong>{{ __('home.umrah_packages') }}</strong>
                                <small>{{ __('home.umrah_short') }}</small>
                            </div>
                        </a>

                        <a href="{{ route('website.visas') }}">
                            <span>02</span>
                            <div>
                                <strong>{{ __('home.visit_visas') }}</strong>
                                <small>{{ __('home.visas_short') }}</small>
                            </div>
                        </a>

                        <a href="{{ route('website.tickets') }}">
                            <span>03</span>
                            <div>
                                <strong>{{ __('home.air_ticketing') }}</strong>
                                <small>{{ __('home.tickets_short') }}</small>
                            </div>
                        </a>

                        <a href="{{ route('website.hotels') }}">
                            <span>04</span>
                            <div>
                                <strong>{{ __('home.hotel_booking') }}</strong>
                                <small>{{ __('home.hotels_short') }}</small>
                            </div>
                        </a>
                    </div>

                    <a href="{{ route('website.services') }}" class="kes-v2-panel-link">
                        {{ __('home.view_all_services') }}
                        <span>&rarr;</span>
                    </a>
                </aside>
            </div>
        </section>

        <section class="kes-v2-intro">
            <div class="kes-v2-container kes-v2-intro-grid">
                <div>
                    <span class="kes-v2-section-label">{{ __('home.welcome') }}</span>
                    <h2>{{ __('home.intro_title') }}</h2>
                </div>

                <div>
                    <p>
                        {{ __('home.intro_text') }}
                    </p>

                    <a href="{{ route('website.about') }}" class="kes-v2-text-link">
                        {{ __('home.learn_about') }}
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="kes-v2-services">
            <div class="kes-v2-container">
                <div class="kes-v2-section-heading">
                    <div>
                        <span class="kes-v2-section-label">{{ __('home.what_we_offer') }}</span>
                        <h2>{{ __('home.solutions_title') }}</h2>
                    </div>

                    <p>
                        {{ __('home.solutions_text') }}
                    </p>
                </div>

                <div class="kes-v2-service-grid">
                    <a href="{{ route('website.umrah') }}" class="kes-v2-service-card featured">
                        <span class="kes-v2-service-number">01</span>
                        <div class="kes-v2-service-symbol">UM</div>
                        <h3>{{ __('home.umrah_packages') }}</h3>
                        <p>
                            {{ __('home.umrah_description') }}
                        </p>
                        <span class="kes-v2-card-link">{{ __('home.explore_umrah') }} &rarr;</span>
                    </a>

                    <a href="{{ route('website.visas') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">02</span>
                        <div class="kes-v2-service-symbol">VS</div>
                        <h3>{{ __('home.visit_visas') }}</h3>
                        <p>
                            {{ __('home.visa_description') }}
                        </p>
                        <span class="kes-v2-card-link">{{ __('home.visa_services') }} &rarr;</span>
                    </a>

                    <a href="{{ route('website.tickets') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">03</span>
                        <div class="kes-v2-service-symbol">AT</div>
                        <h3>{{ __('home.air_ticketing') }}</h3>
                        <p>
                            {{ __('home.ticket_description') }}
                        </p>
                        <span class="kes-v2-card-link">{{ __('home.book_tickets') }} &rarr;</span>
                    </a>

                    <a href="{{ route('website.hotels') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">04</span>
                        <div class="kes-v2-service-symbol">HB</div>
                        <h3>{{ __('home.hotel_booking') }}</h3>
                        <p>
                            {{ __('home.hotel_description') }}
                        </p>
                        <span class="kes-v2-card-link">{{ __('home.find_hotels') }} &rarr;</span>
                    </a>

                    <a href="{{ route('website.insurance') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">05</span>
                        <div class="kes-v2-service-symbol">TI</div>
                        <h3>{{ __('home.travel_insurance') }}</h3>
                        <p>
                            {{ __('home.insurance_description') }}
                        </p>
                        <span class="kes-v2-card-link">{{ __('home.get_guidance') }} &rarr;</span>
                    </a>

                    <a href="{{ route('website.tours') }}" class="kes-v2-service-card">
                        <span class="kes-v2-service-number">06</span>
                        <div class="kes-v2-service-symbol">PT</div>
                        <h3>{{ __('home.pakistan_tours') }}</h3>
                        <p>
                            {{ __('home.tours_description') }}
                        </p>
                        <span class="kes-v2-card-link">{{ __('home.explore_pakistan') }} &rarr;</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="kes-v2-why">
            <div class="kes-v2-container kes-v2-why-grid">
                <div class="kes-v2-why-card">
                    <span class="kes-v2-section-label light">{{ __('home.why_choose_us') }}</span>

                    <h2>{{ __('home.trusted_name') }}</h2>

                    <p>
                        {{ __('home.trusted_text') }}
                    </p>

                    <a href="{{ route('website.contact') }}" class="kes-v2-btn kes-v2-btn-gold">
                        {{ __('home.start_inquiry') }}
                    </a>
                </div>

                <div class="kes-v2-benefit-grid">
                    <article>
                        <span>01</span>
                        <h3>{{ __('home.clear_guidance') }}</h3>
                        <p>{{ __('home.clear_guidance_text') }}</p>
                    </article>

                    <article>
                        <span>02</span>
                        <h3>{{ __('home.customer_support') }}</h3>
                        <p>{{ __('home.support_text') }}</p>
                    </article>

                    <article>
                        <span>03</span>
                        <h3>{{ __('home.reliable_planning') }}</h3>
                        <p>{{ __('home.planning_text') }}</p>
                    </article>

                    <article>
                        <span>04</span>
                        <h3>{{ __('home.multiple_services') }}</h3>
                        <p>{{ __('home.multiple_services_text') }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="kes-v2-process">
            <div class="kes-v2-container">
                <div class="kes-v2-section-heading centered">
                    <div>
                        <span class="kes-v2-section-label">{{ __('home.simple_process') }}</span>
                        <h2>{{ __('home.process_title') }}</h2>
                    </div>
                </div>

                <div class="kes-v2-process-grid">
                    <article>
                        <span>01</span>
                        <h3>{{ __('home.send_inquiry') }}</h3>
                        <p>{{ __('home.send_inquiry_text') }}</p>
                    </article>

                    <article>
                        <span>02</span>
                        <h3>{{ __('home.professional_guidance') }}</h3>
                        <p>{{ __('home.professional_guidance_text') }}</p>
                    </article>

                    <article>
                        <span>03</span>
                        <h3>{{ __('home.confirm_booking') }}</h3>
                        <p>{{ __('home.confirm_booking_text') }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="kes-v2-cta">
            <div class="kes-v2-container">
                <div class="kes-v2-cta-inner">
                    <div>
                        <span>{{ __('home.ready_text') }}</span>
                        <h2>{{ __('home.speak_team') }}</h2>
                    </div>

                    <div class="kes-v2-cta-actions">
                        <a href="{{ route('website.contact') }}" class="kes-v2-btn kes-v2-btn-gold">
                            {{ __('home.contact_us') }}
                        </a>

                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                           target="_blank"
                           rel="noopener"
                           class="kes-v2-btn kes-v2-btn-outline-white">
                            {{ __('home.whatsapp') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>
@endsection