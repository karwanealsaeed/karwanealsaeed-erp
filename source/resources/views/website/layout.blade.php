<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ur') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#0B1F3A">
    <meta name="application-name" content="KES Travel">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="KES Travel">

    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link
        rel="apple-touch-icon"
        sizes="180x180"
        href="{{ asset('images/pwa/apple-touch-icon.png') }}"
    >
    <link
        rel="icon"
        type="image/png"
        sizes="32x32"
        href="{{ asset('images/pwa/favicon-32x32.png') }}"
    >
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    @php
        $seoTitle = $title ?? 'Karwan E Al Saeed Travel & Tours';
        $seoDescription = $description ?? 'Karwan E Al Saeed Travel & Tours';
        $seoBaseCanonical = $canonical ?? url()->current();
        $seoEnglishUrl = rtrim($seoBaseCanonical, '?&');
        $seoUrduUrl = $seoEnglishUrl
            .(str_contains($seoEnglishUrl, '?') ? '&' : '?')
            .'lang=ur';
        $seoCanonical = app()->isLocale('ur')
            ? $seoUrduUrl
            : $seoEnglishUrl;

        $businessLogo = $company?->logo_url
            ?: asset('images/logo.png');

        $seoImage = $seoImage ?? $businessLogo;
        $seoType = $seoType ?? 'website';
        $seoRobots = $seoRobots
            ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        $businessSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'TravelAgency',
            '@id' => url('/').'#travel-agency',
            'name' => $company?->company_name
                ?: 'Karwan E Al Saeed Travel & Tours',
            'alternateName' => $company?->short_name ?: 'KES Travel',
            'url' => url('/'),
            'logo' => $businessLogo,
            'image' => $businessLogo,
            'telephone' => $company?->phone ?: $company?->mobile,
            'email' => $company?->email,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $company?->address,
                'addressLocality' => $company?->city,
                'addressCountry' => $company?->country ?: 'Pakistan',
            ],
        ];

        $businessSchema = array_filter(
            $businessSchema,
            static fn ($value): bool => filled($value),
        );

        $businessSchema['address'] = array_filter(
            $businessSchema['address'],
            static fn ($value): bool => filled($value),
        );
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <link rel="alternate" hreflang="en-PK" href="{{ $seoEnglishUrl }}">
    <link rel="alternate" hreflang="ur-PK" href="{{ $seoUrduUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $seoEnglishUrl }}">

    <meta property="og:locale" content="{{ app()->isLocale('ur') ? 'ur_PK' : 'en_PK' }}">
    <meta property="og:locale:alternate" content="{{ app()->isLocale('ur') ? 'en_PK' : 'ur_PK' }}">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:site_name" content="{{ $company?->company_name ?: 'Karwan E Al Saeed Travel & Tours' }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:alt" content="{{ $seoTitle }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    <script type="application/ld+json">
        {!! json_encode(
            $businessSchema,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_PRETTY_PRINT
        ) !!}
    </script>

    @if (! empty($seoSchema ?? null))
        <script type="application/ld+json">
            {!! json_encode(
                $seoSchema,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_PRETTY_PRINT
            ) !!}
        </script>
    @endif

    @stack('seo')
    <link rel="stylesheet" href="{{ asset('css/website-theme.css') }}?v={{ file_exists(public_path('css/website-theme.css')) ? filemtime(public_path('css/website-theme.css')) : time() }}">
<link rel="stylesheet" href="{{ asset('css/karwan-ai.css') }}?v={{ file_exists(public_path('css/karwan-ai.css')) ? filemtime(public_path('css/karwan-ai.css')) : time() }}">
</head>
<body class="kes-public-site kes-locale-{{ app()->getLocale() }}">
    <header class="kes-site-header">
        <div class="kes-topbar">
            <div class="kes-topbar-inner">
                <div class="kes-topbar-left">
                    <span class="kes-topbar-label">{{ __('website.office') }}:</span>
                    <span>{{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}</span>
                </div>

                <div class="kes-topbar-right">
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->mobile ?? '+923012592342') }}">
                        <span class="kes-topbar-label">{{ __('website.call_us') }}:</span>
                        {{ $company->mobile ?? '+92 301 2592342' }}
                    </a>

                    <span class="kes-topbar-divider"></span>

                    <a href="mailto:{{ $company->email ?? 'karwanealsaeed@gmail.com' }}">
                        <span class="kes-topbar-label">{{ __('website.email_us') }}:</span>
                        {{ $company->email ?? 'karwanealsaeed@gmail.com' }}
                    </a>
                </div>
            </div>
        </div>

        <nav class="kes-navbar" aria-label="Main navigation">
            <div class="kes-navbar-inner">
                <a href="{{ route('website.home') }}" class="kes-brand">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Karwan E Al Saeed Travel & Tours"
                        class="kes-logo-img"
                    >

                    <span class="kes-brand-copy">
                        <span class="kes-brand-title">
                            {{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}
                        </span>

                        <span class="kes-brand-subtitle">
                            {{ __('website.brand_services') }}
                        </span>
                    </span>
                </a>

                <div class="kes-nav-links kes-desktop-nav">
                    <a href="{{ route('website.home') }}"
                       class="{{ request()->routeIs('website.home') ? 'is-active' : '' }}">
                        {{ __('website.home') }}
                    </a>

                    <a href="{{ route('website.about') }}"
                       class="{{ request()->routeIs('website.about') ? 'is-active' : '' }}">
                        {{ __('website.about') }}
                    </a>

                    <a href="{{ route('website.services') }}"
                       class="{{ request()->routeIs('website.services') ? 'is-active' : '' }}">
                        {{ __('website.services') }}
                    </a>

                    <a href="{{ route('website.umrah') }}"
                       class="{{ request()->routeIs('website.umrah*') ? 'is-active' : '' }}">
                        {{ __('website.umrah') }}
                    </a>

                    <a href="{{ route('website.visas') }}"
                       class="{{ request()->routeIs('website.visas') ? 'is-active' : '' }}">
                        {{ __('website.visas') }}
                    </a>

                    <a href="{{ route('website.tickets') }}"
                       class="{{ request()->routeIs('website.tickets') ? 'is-active' : '' }}">
                        {{ __('website.tickets') }}
                    </a>

                    <a href="{{ route('website.hotels') }}"
                       class="{{ request()->routeIs('website.hotels') ? 'is-active' : '' }}">
                        {{ __('website.hotels') }}
                    </a>

                    <a href="{{ route('website.tours') }}"
                       class="{{ request()->routeIs('website.tours*') ? 'is-active' : '' }}">
                        {{ __('website.tours') }}
                    </a>


                    <a href="{{ route('website.contact') }}"
                       class="{{ request()->routeIs('website.contact') ? 'is-active' : '' }}">
                        {{ __('website.contact_us') }}
                    </a>

                                        <div class="kes-language-switcher"
                         aria-label="{{ __('website.language') }}">
                        <a href="{{ route('website.language', 'en') }}"
                           class="{{ app()->isLocale('en') ? 'is-active' : '' }}">
                            EN
                        </a>

                        <a href="{{ route('website.language', 'ur') }}"
                           class="{{ app()->isLocale('ur') ? 'is-active' : '' }}">
                            اردو
                        </a>
                    </div>

                    <details class="kes-login-menu">
                        <summary>{{ __('website.login') }}</summary>

                        <div class="kes-login-dropdown">
                            <a href="{{ route('customer.portal.login') }}">{{ __('website.customer_login') }}</a>
                            <a href="/admin">{{ __('website.admin_login') }}</a>
                        </div>
                    </details>
                </div>

                <details class="kes-mobile-nav">
                    <summary>
                        <span>{{ __('website.menu') }}</span>
                        <span class="kes-menu-lines" aria-hidden="true"></span>
                    </summary>

                    <div class="kes-mobile-nav-panel">
                        <a href="{{ route('website.home') }}">{{ __('website.home') }}</a>
                        <a href="{{ route('website.about') }}">{{ __('website.about_us') }}</a>
                        <a href="{{ route('website.services') }}">{{ __('website.our_services') }}</a>
                        <a href="{{ route('website.umrah') }}">{{ __('website.umrah_packages') }}</a>
                        <a href="{{ route('website.visas') }}">{{ __('website.visit_visas') }}</a>
                        <a href="{{ route('website.tickets') }}">{{ __('website.air_ticketing') }}</a>
                        <a href="{{ route('website.hotels') }}">{{ __('website.hotel_booking') }}</a>
                        <a href="{{ route('website.tours') }}">{{ __('website.pakistan_tours') }}</a>
                        <a href="{{ route('website.gallery') }}">{{ __('gallery.title') }}</a>
                        <a href="{{ route('website.contact') }}">{{ __('website.contact_us') }}</a>

                                                <div class="kes-mobile-language-switcher">
                            <span>{{ __('website.language') }}</span>

                            <div>
                                <a href="{{ route('website.language', 'en') }}"
                                   class="{{ app()->isLocale('en') ? 'is-active' : '' }}">
                                    English
                                </a>

                                <a href="{{ route('website.language', 'ur') }}"
                                   class="{{ app()->isLocale('ur') ? 'is-active' : '' }}">
                                    اردو
                                </a>
                            </div>
                        </div>

                        <div class="kes-mobile-login-links">
                            <a href="{{ route('customer.portal.login') }}">{{ __('website.customer_login') }}</a>
                            <a href="/admin">{{ __('website.admin_login') }}</a>
                        </div>
                    </div>
                </details>
            </div>
        </nav>
    </header>

    @yield('content')
    <footer class="kes-footer kes-footer-v2">
        <div class="kes-footer-main">
            <div class="kes-footer-grid-v2">

                <div class="kes-footer-company">
                    <a href="{{ route('website.home') }}" class="kes-footer-brand">
                        <img src="{{ asset('images/logo.png') }}"
                             alt="Karwan E Al Saeed Travel & Tours">

                        <span>
                            <strong>
                                {{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}
                            </strong>

                            <small>{{ __('website.trusted_partner') }}</small>
                        </span>
                    </a>

                    <p>
                        {{ __('website.company_description') }}
                    </p>

                    <a class="kes-footer-whatsapp"
                       href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                       target="_blank"
                       rel="noopener">
                        {{ __('website.chat_whatsapp') }}
                    </a>
                </div>

                <div class="kes-footer-column">
                    <h3>{{ __('website.quick_links') }}</h3>

                    <a href="{{ route('website.home') }}">{{ __('website.home') }}</a>
                    <a href="{{ route('website.about') }}">{{ __('website.about_us') }}</a>
                    <a href="{{ route('website.services') }}">{{ __('website.our_services') }}</a>
                    <a href="{{ route('website.gallery') }}">{{ __('gallery.title') }}</a>
                    <a href="{{ route('website.promotions') }}" class="{{ request()->routeIs('website.promotions*') ? 'is-active' : '' }}">{{ __('promotions.title') }}</a>
                    <a href="{{ route('website.contact') }}">{{ __('website.contact_us') }}</a>
                    <a href="{{ route('customer.portal.login') }}">{{ __('website.customer_portal') }}</a>
                </div>

                <div class="kes-footer-column">
                    <h3>{{ __('website.our_services') }}</h3>

                    <a href="{{ route('website.umrah') }}">{{ __('website.umrah_packages') }}</a>
                    <a href="{{ route('website.visas') }}">{{ __('website.visit_visas') }}</a>
                    <a href="{{ route('website.tickets') }}">{{ __('website.air_ticketing') }}</a>
                    <a href="{{ route('website.hotels') }}">{{ __('website.hotel_booking') }}</a>
                    <a href="{{ route('website.tours') }}">{{ __('website.pakistan_tours') }}</a>
                </div>

                <div class="kes-footer-column kes-footer-contact">
                    <h3>{{ __('website.contact_information') }}</h3>

                    <div>
                        <span>{{ __('website.office') }}</span>
                        <p>
                            {{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}
                        </p>
                    </div>

                    <div>
                        <span>{{ __('website.phone') }}</span>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->mobile ?? '+923012592342') }}">
                            {{ $company->mobile ?? '+92 301 2592342' }}
                        </a>
                    </div>

                    <div>
                        <span>{{ __('website.email') }}</span>
                        <a href="mailto:{{ $company->email ?? 'karwanealsaeed@gmail.com' }}">
                            {{ $company->email ?? 'karwanealsaeed@gmail.com' }}
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <div class="kes-footer-bottom">
            <div>
                <p>
                    &copy; {{ date('Y') }}
                    {{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}.
                    {{ __('website.copyright') }}
                </p>

                <p>
                    {{ __('website.brand_services') }}
                </p>
            </div>
        </div>
    </footer>

    <a class="kes-whatsapp"
       href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
       target="_blank">
        WA
    </a>
<div class="kes-support-widget">
    <details class="kes-support-box"
        @if(session('support_success') || session('support_error') || $errors->supportInquiry->any()) open @endif
    >
        <summary>💬 {{ __('website.support_widget') }}</summary>

        <div class="kes-support-panel">
            <div class="kes-support-head">
                <strong>{{ __('website.support_help_heading') }}</strong>
                <span>{{ __('website.support_team_name') }}</span>
            </div>

            @if (session('support_success'))
                <div class="kes-support-alert success">
                    {{ session('support_success') }}
                </div>
            @endif

            @if (session('support_reply'))
                <div class="kes-support-alert reply">
                    {{ session('support_reply') }}
                </div>
            @endif

            @if (session('support_error'))
                <div class="kes-support-alert error">
                    {{ session('support_error') }}
                </div>
            @endif

            @if ($errors->supportInquiry->any())
                <div class="kes-support-alert error">
                    @foreach ($errors->supportInquiry->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('support-inquiries.store') }}">
                @csrf

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="{{ __('website.support_name_placeholder') }}"
                    required
                >

                <input
                    type="text"
                    name="mobile"
                    value="{{ old('mobile') }}"
                    placeholder="{{ __('website.support_mobile_placeholder') }}"
                    required
                >

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('website.support_email_placeholder') }}"
                >

                <select name="service_type">
                    <option value="">
                        {{ __('website.support_select_service') }}
                    </option>

                    <option value="Umrah Package"
                        @selected(old('service_type') === 'Umrah Package')>
                        {{ __('website.support_umrah_package') }}
                    </option>

                    <option value="Visit Visa"
                        @selected(old('service_type') === 'Visit Visa')>
                        {{ __('website.support_visit_visa') }}
                    </option>

                    <option value="Air Ticketing"
                        @selected(old('service_type') === 'Air Ticketing')>
                        {{ __('website.air_ticketing') }}
                    </option>

                    <option value="Hotel Booking"
                        @selected(old('service_type') === 'Hotel Booking')>
                        {{ __('website.hotel_booking') }}
                    </option>

                    <option value="Travel Insurance"
                        @selected(old('service_type') === 'Travel Insurance')>
                        {{ __('website.support_travel_insurance') }}
                    </option>

                    <option value="Pakistan Tours"
                        @selected(old('service_type') === 'Pakistan Tours')>
                        {{ __('website.pakistan_tours') }}
                    </option>
                </select>

                <input
                    type="text"
                    name="subject"
                    value="{{ old('subject') }}"
                    placeholder="{{ __('website.support_subject_placeholder') }}"
                >

                <textarea
                    name="message"
                    rows="4"
                    placeholder="{{ __('website.support_message_placeholder') }}"
                    required
                >{{ old('message') }}</textarea>

                <button type="submit">
                    {{ __('website.support_submit') }}
                </button>
            </form>

            <a class="kes-support-whatsapp"
               href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
               target="_blank"
               rel="noopener">
                {{ __('website.support_whatsapp_now') }}
            </a>
        </div>
    </details>
</div>

@include('components.karwan-ai-widget')

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker
                .register('/service-worker.js', {
                    scope: '/',
                })
                .catch((error) => {
                    console.error(
                        'Service worker registration failed:',
                        error,
                    );
                });
        });
    }
</script>
</body>
</html>
