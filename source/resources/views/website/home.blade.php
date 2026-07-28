@extends('website.home-v3-layout')

@php
    /*
     * Child Blade sections are evaluated before their parent layout.
     * Keep variables used inside this homepage section locally scoped.
     */
    $homeV3Company = $company ?? null;
    $homeV3Logo = asset('images/logo.png');

    $homeV3Phone =
        data_get($homeV3Company, 'mobile')
        ?: (data_get($homeV3Company, 'phone') ?: '+92 301 2592342');

    $homeV3WhatsApp =
        preg_replace(
            '/[^0-9]/',
            '',
            data_get($homeV3Company, 'whatsapp') ?: $homeV3Phone,
        )
        ?: '923012592342';
@endphp

@section('content')
<main id="main" class="kes-premium-studio-home">
    <section class="hero" data-hero>
      <div class="hero__image" data-hero-image aria-hidden="true"></div>
      <div class="hero__veil" aria-hidden="true"></div>
      <div class="hero__aurora hero__aurora--one" aria-hidden="true"></div>
      <div class="hero__aurora hero__aurora--two" aria-hidden="true"></div>
      <div class="hero__pattern" aria-hidden="true"></div>

      <div class="shell hero__layout">
        <div class="hero__content">
          <div class="hero__kicker reveal" data-reveal>
            <span class="pulse-dot" aria-hidden="true"></span>
            {{ __('premium-studio.hero_eyebrow') }}
          </div>

          <p class="hero__arabic reveal" lang="ar" dir="rtl" data-reveal>
            لَبَّيْكَ اللَّهُمَّ لَبَّيْكَ
          </p>

          <h1 class="hero__title reveal" data-reveal>
            {{ __('premium-studio.hero_title_prefix') }}
            <span>{{ __('premium-studio.hero_title_highlight') }}</span>
          </h1>

          <p class="hero__lead reveal" data-reveal>
            {{ __('premium-studio.hero_lead') }}
          </p>

          <p class="hero__urdu reveal" lang="ur" dir="rtl" data-reveal>
            {{ __('premium-studio.hero_urdu') }}
          </p>

          <div class="hero__actions reveal" data-reveal>
            <a class="btn btn--gold btn--large magnetic" href="{{ route('website.umrah') }}">
              <span>{{ __('premium-studio.explore_packages') }}</span>
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <a class="btn btn--glass btn--large magnetic" href="{{ route('website.contact') }}">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 10h8M8 14h5M20 12a8 8 0 1 1-3.1-6.3L20 4v5h-5l1.8-1.8"/></svg>
              <span>{{ __('premium-studio.speak_team') }}</span>
            </a>
          </div>

          <div class="hero__trust reveal" data-reveal>
            <article>
              <span class="icon-disc">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 7 4v5c0 4.7-2.8 7.5-7 9-4.2-1.5-7-4.3-7-9V7l7-4Z"/><path d="m9 12 2 2 4-4"/></svg>
              </span>
              <div><strong>{{ __('premium-studio.trusted_guidance') }}</strong><small>{{ __('premium-studio.trusted_guidance_text') }}</small></div>
            </article>
            <article>
              <span class="icon-disc">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 15h16M6 15l1-7h10l1 7M8 8V5h8v3M7 19h10"/></svg>
              </span>
              <div><strong>{{ __('premium-studio.complete_service') }}</strong><small>{{ __('premium-studio.complete_service_text') }}</small></div>
            </article>
            <article>
              <span class="icon-disc">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/><path d="M12 7v5l3 2"/></svg>
              </span>
              <div><strong>{{ __('premium-studio.responsive_team') }}</strong><small>{{ __('premium-studio.responsive_team_text') }}</small></div>
            </article>
          </div>
        </div>

        <aside class="journey-console tilt reveal" data-tilt data-reveal>
          <div class="journey-console__glow" aria-hidden="true"></div>
          <header>
            <div class="console-brand">
              <span class="console-brand__mark">
                <img src="{{ $homeV3Logo }}" alt="" onerror="this.style.display='none'">
                <b>KES</b>
              </span>
              <div><small>{{ __('premium-studio.personal_travel_desk') }}</small><strong>{{ __('premium-studio.begin_journey') }}</strong></div>
            </div>
            <span class="live-badge"><i></i> {{ __('premium-studio.team_online') }}</span>
          </header>

          <div class="console-grid">
            <a class="console-card magnetic" href="{{ route('website.umrah') }}">
              <span class="console-card__icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 20V9l5-5 5 5v11M9 20v-6h6v6M5 20h14"/></svg>
              </span>
              <div><small>01</small><strong>{{ __('premium-studio.umrah_packages_lower') }}</strong><p>{{ __('premium-studio.umrah_support') }}</p></div>
              <svg class="arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <a class="console-card magnetic" href="{{ route('website.services') }}">
              <span class="console-card__icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h8l3 3v15H7z"/><path d="M15 3v4h4M10 12h5M10 16h5"/></svg>
              </span>
              <div><small>02</small><strong>{{ __('premium-studio.visit_visas_lower') }}</strong><p>{{ __('premium-studio.visa_guidance') }}</p></div>
              <svg class="arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <a class="console-card magnetic" href="{{ route('website.services') }}">
              <span class="console-card__icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 18-6-7 15-2-6-6-2z"/><path d="m12 14 3-3"/></svg>
              </span>
              <div><small>03</small><strong>{{ __('premium-studio.air_ticketing_lower') }}</strong><p>{{ __('premium-studio.ticket_guidance') }}</p></div>
              <svg class="arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
          </div>

          <div class="console-contact">
            <div class="avatar-stack" aria-hidden="true">
              <span>K</span><span>E</span><span>S</span>
            </div>
            <p><strong>{{ __('premium-studio.need_help') }}</strong><small>{{ __('premium-studio.travel_desk_ready') }}</small></p>
            <a class="mini-action magnetic" href="{{ route('website.contact') }}" aria-label="{{ __('premium-studio.contact_travel_desk') }}">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
          </div>
        </aside>
      </div>

      <div class="hero__scroll" aria-hidden="true">
        <span>{{ __('premium-studio.scroll_explore') }}</span><i></i>
      </div>
    </section>

    <section class="marquee" aria-label="{{ __('premium-studio.services') }}">
      <div class="marquee__track">
        <span>{{ __('premium-studio.umrah_packages') }}</span><i></i><span>{{ __('premium-studio.visit_visas') }}</span><i></i><span>{{ __('premium-studio.air_ticketing') }}</span><i></i><span>{{ __('premium-studio.hotel_booking') }}</span><i></i><span>{{ __('premium-studio.travel_insurance') }}</span><i></i><span>{{ __('premium-studio.pakistan_tours') }}</span><i></i>
        <span>{{ __('premium-studio.umrah_packages') }}</span><i></i><span>{{ __('premium-studio.visit_visas') }}</span><i></i><span>{{ __('premium-studio.air_ticketing') }}</span><i></i><span>{{ __('premium-studio.hotel_booking') }}</span><i></i><span>{{ __('premium-studio.travel_insurance') }}</span><i></i><span>{{ __('premium-studio.pakistan_tours') }}</span><i></i>
      </div>
    </section>

    <section class="section intro">
      <div class="shell intro__grid">
        <div class="section-heading reveal" data-reveal>
          <span class="eyebrow">{{ __('premium-studio.intro_eyebrow') }}</span>
          <h2>{{ __('premium-studio.intro_title') }}</h2>
        </div>
        <div class="intro__copy reveal" data-reveal>
          <p>{{ __('premium-studio.intro_text') }}</p>
          <div class="signature-line">
            <span></span>
            <strong>{{ __('premium-studio.travel_confidence') }}</strong>
          </div>
        </div>
      </div>
    </section>

    <section class="section umrah" id="umrah">
      <div class="shell">
        <header class="section-head reveal" data-reveal>
          <div>
            <span class="eyebrow">{{ __('premium-studio.package_eyebrow') }}</span>
            <h2>{{ __('premium-studio.package_title') }}</h2>
          </div>
          <p>{{ __('premium-studio.package_intro') }}</p>
        </header>

        <div class="package-showcase">
          <article class="package-hero tilt reveal" data-tilt data-reveal>
            <div class="package-hero__media" aria-hidden="true"></div>
            <div class="package-hero__shade" aria-hidden="true"></div>
            <div class="package-hero__top">
              <span class="premium-badge"><i></i> {{ __('premium-studio.featured_journey') }}</span>
              <span class="availability"><i></i> {{ __('premium-studio.enquiries_open') }}</span>
            </div>
            <div class="package-hero__body">
              <p class="package-code">KES-UMR-2026</p>
              <h3>{{ __('premium-studio.signature_umrah') }}</h3>
              <p>{{ __('premium-studio.signature_text') }}</p>
              <div class="package-meta">
                <span><b>14</b><small>{{ __('premium-studio.nights') }}</small></span>
                <span><b>2</b><small>{{ __('premium-studio.holy_cities') }}</small></span>
                <span><b>24/7</b><small>{{ __('premium-studio.guidance') }}</small></span>
              </div>
              <div class="package-actions">
                <a class="btn btn--gold magnetic" href="{{ route('website.umrah') }}"><span>{{ __('premium-studio.view_package') }}</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                <a class="text-action magnetic" href="{{ route('website.contact') }}">{{ __('premium-studio.request_price') }}</a>
              </div>
            </div>
          </article>

          <div class="package-list">
            <article class="package-row tilt reveal" data-tilt data-reveal>
              <div class="package-row__number">01</div>
              <div class="package-row__copy">
                <small>{{ __('premium-studio.family_friendly') }}</small>
                <h3>{{ __('premium-studio.economy_umrah') }}</h3>
                <p>{{ __('premium-studio.economy_text') }}</p>
              </div>
              <div class="package-row__facts"><span>{{ __('premium-studio.ten_nights') }}</span><span>{{ __('premium-studio.hotel_included') }}</span></div>
              <a class="round-link magnetic" href="{{ route('website.umrah') }}" aria-label="{{ __('premium-studio.view_economy') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </article>
            <article class="package-row tilt reveal" data-tilt data-reveal>
              <div class="package-row__number">02</div>
              <div class="package-row__copy">
                <small>{{ __('premium-studio.enhanced_comfort') }}</small>
                <h3>{{ __('premium-studio.executive_umrah') }}</h3>
                <p>{{ __('premium-studio.executive_text') }}</p>
              </div>
              <div class="package-row__facts"><span>{{ __('premium-studio.fourteen_nights') }}</span><span>{{ __('premium-studio.premium_hotel') }}</span></div>
              <a class="round-link magnetic" href="{{ route('website.umrah') }}" aria-label="{{ __('premium-studio.view_executive') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </article>
            <article class="package-row tilt reveal" data-tilt data-reveal>
              <div class="package-row__number">03</div>
              <div class="package-row__copy">
                <small>{{ __('premium-studio.built_around_you') }}</small>
                <h3>{{ __('premium-studio.custom_umrah') }}</h3>
                <p>{{ __('premium-studio.custom_text') }}</p>
              </div>
              <div class="package-row__facts"><span>{{ __('premium-studio.flexible') }}</span><span>{{ __('premium-studio.private_planning') }}</span></div>
              <a class="round-link magnetic" href="{{ route('website.umrah') }}" aria-label="{{ __('premium-studio.view_custom') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="section services" id="services">
      <div class="services__light services__light--left" aria-hidden="true"></div>
      <div class="services__light services__light--right" aria-hidden="true"></div>
      <div class="shell">
        <header class="section-head section-head--light reveal" data-reveal>
          <div>
            <span class="eyebrow">{{ __('premium-studio.services_eyebrow') }}</span>
            <h2>{{ __('premium-studio.services_title') }}</h2>
          </div>
          <p>{{ __('premium-studio.services_intro') }}</p>
        </header>

        <div class="service-bento">
          <a class="service-tile service-tile--primary tilt reveal" data-tilt data-reveal href="{{ route('website.umrah') }}">
            <span class="service-tile__number">01</span>
            <span class="service-tile__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 20V9l5-5 5 5v11M9 20v-6h6v6M5 20h14"/></svg></span>
            <div><small>{{ __('premium-studio.spiritual_journeys') }}</small><h3>{{ __('premium-studio.umrah_packages') }}</h3><p>{{ __('premium-studio.umrah_service_text') }}</p></div>
            <span class="service-tile__arrow"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <a class="service-tile service-tile--gold tilt reveal" data-tilt data-reveal href="{{ route('website.visas') }}">
            <span class="service-tile__number">02</span>
            <span class="service-tile__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h8l3 3v15H7z"/><path d="M15 3v4h4M10 12h5M10 16h5"/></svg></span>
            <div><small>{{ __('premium-studio.documentation') }}</small><h3>{{ __('premium-studio.visit_visas') }}</h3><p>{{ __('premium-studio.visa_service_text') }}</p></div>
            <span class="service-tile__arrow"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <a class="service-tile tilt reveal" data-tilt data-reveal href="{{ route('website.tickets') }}">
            <span class="service-tile__number">03</span>
            <span class="service-tile__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 18-6-7 15-2-6-6-2z"/><path d="m12 14 3-3"/></svg></span>
            <div><small>{{ __('premium-studio.worldwide_routes') }}</small><h3>{{ __('premium-studio.air_ticketing') }}</h3><p>{{ __('premium-studio.ticket_service_text') }}</p></div>
            <span class="service-tile__arrow"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <a class="service-tile tilt reveal" data-tilt data-reveal href="{{ route('website.hotels') }}">
            <span class="service-tile__number">04</span>
            <span class="service-tile__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V8h16v11M4 14h16M7 11h4M4 19v2M20 19v2"/></svg></span>
            <div><small>{{ __('premium-studio.trusted_stays') }}</small><h3>{{ __('premium-studio.hotel_booking') }}</h3><p>{{ __('premium-studio.hotel_service_text') }}</p></div>
            <span class="service-tile__arrow"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <a class="service-tile service-tile--wide tilt reveal" data-tilt data-reveal href="{{ route('website.insurance') }}">
            <span class="service-tile__number">05</span>
            <span class="service-tile__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 7 4v5c0 4.7-2.8 7.5-7 9-4.2-1.5-7-4.3-7-9V7l7-4Z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <div><small>{{ __('premium-studio.travel_protection') }}</small><h3>{{ __('premium-studio.insurance_assistance') }}</h3><p>{{ __('premium-studio.insurance_service_text') }}</p></div>
            <span class="service-tile__arrow"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>

          <a class="service-tile service-tile--wide tilt reveal" data-tilt data-reveal href="{{ route('website.tours') }}">
            <span class="service-tile__number">06</span>
            <span class="service-tile__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 20 9 8l3 6 3-4 6 10M7 20l4-3 3 2 3-2"/></svg></span>
            <div><small>{{ __('premium-studio.explore_pakistan') }}</small><h3>{{ __('premium-studio.pakistan_tours') }}</h3><p>{{ __('premium-studio.tour_service_text') }}</p></div>
            <span class="service-tile__arrow"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </a>
        </div>
      </div>
    </section>

    <section class="section process" id="process">
      <div class="shell">
        <header class="section-head reveal" data-reveal>
          <div>
            <span class="eyebrow">{{ __('premium-studio.process_eyebrow') }}</span>
            <h2>{{ __('premium-studio.process_title') }}</h2>
          </div>
          <p>{{ __('premium-studio.process_intro') }}</p>
        </header>

        <div class="process-line" aria-hidden="true"><span data-process-line></span></div>
        <div class="process-grid">
          <article class="process-step reveal" data-reveal>
            <span class="process-step__number">01</span>
            <div class="process-step__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v12H8l-4 4z"/><path d="M8 9h8M8 13h5"/></svg></div>
            <h3>{{ __('premium-studio.send_inquiry') }}</h3>
            <p>{{ __('premium-studio.send_inquiry_text') }}</p>
          </article>
          <article class="process-step reveal" data-reveal>
            <span class="process-step__number">02</span>
            <div class="process-step__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9l8-5 8 5v10M8 19v-6h8v6M3 19h18"/></svg></div>
            <h3>{{ __('premium-studio.receive_guidance') }}</h3>
            <p>{{ __('premium-studio.receive_guidance_text') }}</p>
          </article>
          <article class="process-step reveal" data-reveal>
            <span class="process-step__number">03</span>
            <div class="process-step__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12l4 4L19 6"/><path d="M21 12a9 9 0 1 1-5-8"/></svg></div>
            <h3>{{ __('premium-studio.confirm_confidence') }}</h3>
            <p>{{ __('premium-studio.confirm_confidence_text') }}</p>
          </article>
        </div>
      </div>
    </section>

    {{-- PHASE 16D-4F7 LIVE HOMEPAGE CONTENT START --}}
{{-- Retired portal preview replaced by live public content. --}}
{{-- Preserved option route contract: route('customer.portal.login') --}}
@include('website.partials.home-latest-content')
{{-- PHASE 16D-4F7 LIVE HOMEPAGE CONTENT END --}}

    <section class="section final-cta" id="contact">
      <div class="shell">
        <div class="final-cta__card reveal" data-reveal>
          <div class="final-cta__pattern" aria-hidden="true"></div>
          <div class="final-cta__light" aria-hidden="true"></div>
          <div>
            <span class="eyebrow">{{ __('premium-studio.ready_eyebrow') }}</span>
            <h2>{{ __('premium-studio.ready_title') }}</h2>
            <p>{{ __('premium-studio.ready_text') }}</p>
          </div>
          <div class="final-cta__actions">
            <a class="btn btn--gold btn--large magnetic" href="{{ route('website.contact') }}"><span>{{ __('premium-studio.contact_team') }}</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            <a class="btn btn--glass btn--large magnetic" href="https://wa.me/{{ $homeV3WhatsApp }}" target="_blank" rel="noopener noreferrer"><span>{{ __('premium-studio.whatsapp') }}</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 20l-3 1 1-3a8 8 0 1 1 3 2z"/><path d="M9 9c1 3 3 5 6 6"/></svg></a>
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection
