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
    {{-- KES PREMIUM UI FOUNDATION START --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&family=Noto+Naskh+Arabic:wght@400;600;700&display=swap"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="{{ asset('css/karwan-premium-ui.css') }}">
    {{-- KES PREMIUM UI FOUNDATION END --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/karwan-premium-studio-v3.css') }}?v={{ file_exists(public_path('css/karwan-premium-studio-v3.css')) ? filemtime(public_path('css/karwan-premium-studio-v3.css')) : time() }}"
    >
</head>
<body class="page-home kes-public-site kes-locale-{{ app()->getLocale() }}">
    @php
        $homeV3Logo =
            $businessLogo
            ?? $company?->logo_url
            ?? asset('images/logo.png');

        $homeV3Phone =
            $company?->mobile
            ?: ($company?->phone ?: '+92 301 2592342');

        $homeV3PhoneDial =
            preg_replace('/[^0-9+]/', '', $homeV3Phone)
            ?: '+923012592342';

        $homeV3WhatsApp =
            preg_replace(
                '/[^0-9]/',
                '',
                $company?->whatsapp ?: $homeV3Phone,
            )
            ?: '923012592342';

        $homeV3Email =
            $company?->email
            ?: 'karwanealsaeed@gmail.com';

        $homeV3Address =
            $company?->address
            ?: 'Office # 90,91 College Road, Chichawatni';

        $homeV3LegalParameters =
            app()->isLocale('ur')
                ? ['lang' => 'ur']
                : [];
    @endphp

    <canvas
        class="cursor-canvas"
        data-cursor-canvas
        aria-hidden="true"
    ></canvas>
    <div
        class="cursor-dot"
        data-cursor-dot
        aria-hidden="true"
    ></div>
    <div class="noise" aria-hidden="true"></div>

    <a class="skip-link" href="#main">
        {{ __('premium-studio.skip_to_content') }}
    </a>

    <header class="site-header" data-header>
        <div class="utility-bar">
            <div class="shell utility-bar__inner">
                <span>{{ $homeV3Address }}</span>

                <div>
                    <a href="tel:{{ $homeV3PhoneDial }}">
                        {{ $homeV3Phone }}
                    </a>

                    <span
                        class="utility-dot"
                        aria-hidden="true"
                    ></span>

                    <a href="mailto:{{ $homeV3Email }}">
                        {{ $homeV3Email }}
                    </a>
                </div>
            </div>
        </div>

        <nav
            class="nav"
            aria-label="{{ __('premium-studio.primary_navigation') }}"
        >
            <div class="shell nav__inner">
                <a
                    class="brand magnetic"
                    href="{{ route('website.home') }}"
                    aria-label="{{ $company?->company_name ?: 'Karwan E Al Saeed' }}"
                >
                    <span class="brand__mark">
                        <img src="{{ $homeV3Logo }}" alt="">
                        <b>KES</b>
                    </span>

                    <span class="brand__copy">
                        <strong>
                            {{ $company?->company_name ?: 'Karwan E Al Saeed' }}
                        </strong>
                        <small>
                            {{ __('premium-studio.brand_services') }}
                        </small>
                    </span>
                </a>

                <button
                    class="menu-toggle"
                    type="button"
                    aria-expanded="false"
                    aria-controls="main-menu"
                    data-menu-toggle
                >
                    <span></span>
                    <span></span>
                    <b>{{ __('premium-studio.menu') }}</b>
                </button>

                <div
                    class="nav__menu"
                    id="main-menu"
                    data-menu
                >
                    <a
                        class="is-active"
                        href="{{ route('website.home') }}"
                    >
                        {{ __('premium-studio.home') }}
                    </a>

                    <a href="{{ route('website.umrah') }}">
                        {{ __('premium-studio.umrah') }}
                    </a>

                    <a href="{{ route('website.services') }}">
                        {{ __('premium-studio.services') }}
                    </a>

                    <a href="{{ route('website.home') }}#process">
                        {{ __('premium-studio.process') }}
                    </a>

                    <a href="{{ route('customer.portal.login') }}">
                        {{ __('premium-studio.agent_portal') }}
                    </a>

                    <a
                        class="language-switch"
                        href="{{ route('website.language', app()->isLocale('ur') ? 'en' : 'ur') }}"
                        aria-label="{{ __('premium-studio.language') }}"
                    >
                        EN / اردو
                    </a>

                    <a
                        class="btn btn--gold magnetic"
                        href="{{ route('website.contact') }}"
                    >
                        <span>
                            {{ __('premium-studio.plan_journey') }}
                        </span>

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </nav>

        {{-- KES V3 COMPLETE VISIBLE SERVICE TABS START --}}
        <div class="kes-v3-service-tabs"
            aria-label="{{ app()->isLocale('ur') ? 'سفری اور بکنگ خدمات' : 'Travel and booking services' }}"
        >
            <div class="shell kes-v3-service-tabs__track">
                <a href="{{ route('website.umrah') }}">
                    {{ app()->isLocale('ur') ? 'عمرہ پیکیجز اور بکنگ' : 'Umrah Packages & Booking' }}
                </a>
                <a href="{{ route('website.visas') }}">
                    {{ app()->isLocale('ur') ? 'وزٹ ویزا' : 'Visit Visas' }}
                </a>
                <a href="{{ route('website.tickets') }}">
                    {{ app()->isLocale('ur') ? 'ایئر ٹکٹنگ' : 'Air Ticketing' }}
                </a>
                <a href="{{ route('website.hotels') }}">
                    {{ app()->isLocale('ur') ? 'ہوٹل بکنگ' : 'Hotel Booking' }}
                </a>
                <a href="{{ route('website.services') }}">
                    {{ app()->isLocale('ur') ? 'ٹرانسپورٹ معاونت' : 'Transport Assistance' }}
                </a>
                <a href="{{ route('website.insurance') }}">
                    {{ app()->isLocale('ur') ? 'ٹریول انشورنس' : 'Travel Insurance' }}
                </a>
                <a href="{{ route('website.tours') }}">
                    {{ app()->isLocale('ur') ? 'پاکستان ٹورز' : 'Pakistan Tours' }}
                </a>
                <a href="{{ route('website.consultancy') }}">
                    {{ app()->isLocale('ur') ? 'ویزا کنسلٹنسی' : 'Visa Consultancy' }}
                </a>
                <a href="{{ route('website.promotions') }}">
                    {{ app()->isLocale('ur') ? 'پروموشنز' : 'Promotions' }}
                </a>
            </div>
        </div>
        {{-- KES V3 COMPLETE VISIBLE SERVICE TABS END --}}
    </header>

    @yield('content')

    <footer class="footer">
        <div class="shell footer__grid">
            <div class="footer__brand">
                <a
                    class="brand"
                    href="{{ route('website.home') }}"
                >
                    <span class="brand__mark">
                        <img src="{{ $homeV3Logo }}" alt="">
                        <b>KES</b>
                    </span>

                    <span class="brand__copy">
                        <strong>
                            {{ $company?->company_name ?: 'Karwan E Al Saeed' }}
                        </strong>
                        <small>
                            {{ __('premium-studio.brand_services') }}
                        </small>
                    </span>
                </a>

                <p>
                    {{ __('premium-studio.footer_description') }}
                </p>
            </div>

            <div>
                <h3>{{ __('premium-studio.explore') }}</h3>
                <a href="{{ route('website.about') }}">
                    {{ __('premium-studio.about') }}
                </a>
                <a href="{{ route('website.gallery') }}">
                    {{ __('premium-studio.gallery') }}
                </a>
                <a href="{{ route('website.promotions') }}">
                    {{ __('premium-studio.promotions') }}
                </a>
                <a href="{{ route('website.blog') }}">
                    {{ __('premium-studio.blog') }}
                </a>

                <a href="{{ route('website.testimonials') }}">
                    {{ __('website.testimonials') }}
                </a>
            </div>

            <div>
                <h3>{{ __('premium-studio.services') }}</h3>
                <a href="{{ route('website.umrah') }}">
                    {{ __('premium-studio.umrah') }}
                </a>
                <a href="{{ route('website.visas') }}">
                    {{ __('premium-studio.visas') }}
                </a>
                <a href="{{ route('website.tickets') }}">
                    {{ __('premium-studio.tickets') }}
                </a>
                <a href="{{ route('website.hotels') }}">
                    {{ __('premium-studio.hotels') }}
                </a>

                <a href="{{ route('website.insurance') }}">
                    {{ __('premium-studio.travel_insurance') }}
                </a>

                <a href="{{ route('website.tours') }}">
                    {{ __('premium-studio.pakistan_tours') }}
                </a>
            </div>

            <div class="kes-footer-legal">
                <h3>{{ __('premium-studio.support') }}</h3>
                <a href="{{ route('website.contact') }}">
                    {{ __('premium-studio.contact') }}
                </a>
                <a href="{{ route('website.faq') }}">
                    {{ __('premium-studio.faq') }}
                </a>
                <a href="{{ route('customer.portal.login') }}">
                    {{ __('premium-studio.agent_portal') }}
                </a>
                                <a
                    href="{{ route('website.legal.terms', $homeV3LegalParameters) }}"
                >
                    {{ __('premium-studio.terms') }}
                </a>

                <a
                    href="{{ route('website.legal.privacy', $homeV3LegalParameters) }}"
                >
                    {{ __('premium-studio.privacy') }}
                </a>

                <a
                    href="{{ route('website.legal.refund', $homeV3LegalParameters) }}"
                >
                    {{ __('premium-studio.refund') }}
                </a>

                <a
                    href="{{ route('website.legal.cancellation', $homeV3LegalParameters) }}"
                >
                    {{ __('premium-studio.cancellation') }}
                </a>

                <a
                    href="{{ route('website.legal.disclaimer', $homeV3LegalParameters) }}"
                >
                    {{ __('premium-studio.disclaimer') }}
                </a>
            </div>
        </div>

        {{-- KES V3 COMPLETE FOOTER DIRECTORY START --}}
        <div class="shell kes-v3-footer-directory">
            <section>
                <h3>{{ app()->isLocale('ur') ? 'فوری روابط' : 'Quick Links' }}</h3>
                <div>
                    <a href="{{ route('website.home') }}">{{ __('premium-studio.home') }}</a>
                    <a href="{{ route('website.about') }}">{{ __('premium-studio.about') }}</a>
                    <a href="{{ route('website.services') }}">{{ __('premium-studio.services') }}</a>
                    <a href="{{ route('website.promotions') }}">{{ __('premium-studio.promotions') }}</a>
                    <a href="{{ route('website.gallery') }}">{{ __('premium-studio.gallery') }}</a>
                    <a href="{{ route('website.blog') }}">{{ __('premium-studio.blog') }}</a>
                    <a href="{{ route('website.testimonials') }}">{{ __('website.testimonials') }}</a>
                    <a href="{{ route('website.faq') }}">{{ __('premium-studio.faq') }}</a>
                    <a href="{{ route('website.contact') }}">{{ __('premium-studio.contact') }}</a>
                </div>
            </section>
            <section>
                <h3>{{ app()->isLocale('ur') ? 'بکنگ اور خدمات' : 'Bookings & Services' }}</h3>
                <div>
                    <a href="{{ route('website.umrah') }}">{{ app()->isLocale('ur') ? 'عمرہ پیکیجز اور بکنگ' : 'Umrah Packages & Booking' }}</a>
                    <a href="{{ route('website.visas') }}">{{ app()->isLocale('ur') ? 'وزٹ ویزا' : 'Visit Visas' }}</a>
                    <a href="{{ route('website.tickets') }}">{{ app()->isLocale('ur') ? 'ایئر ٹکٹنگ' : 'Air Ticketing' }}</a>
                    <a href="{{ route('website.hotels') }}">{{ app()->isLocale('ur') ? 'ہوٹل بکنگ' : 'Hotel Booking' }}</a>
                    <a href="{{ route('website.services') }}">{{ app()->isLocale('ur') ? 'ٹرانسپورٹ معاونت' : 'Transport Assistance' }}</a>
                    <a href="{{ route('website.insurance') }}">{{ app()->isLocale('ur') ? 'ٹریول انشورنس' : 'Travel Insurance' }}</a>
                    <a href="{{ route('website.tours') }}">{{ app()->isLocale('ur') ? 'پاکستان ٹورز' : 'Pakistan Tours' }}</a>
                    <a href="{{ route('website.consultancy') }}">{{ app()->isLocale('ur') ? 'ویزا کنسلٹنسی' : 'Visa Consultancy' }}</a>
                </div>
            </section>
            <section>
                <h3>{{ app()->isLocale('ur') ? 'پورٹل اور زبان' : 'Portal & Language' }}</h3>
                <div>
                    <a href="{{ route('customer.portal.login') }}">{{ __('premium-studio.agent_portal') }}</a>
                    <a href="{{ route('website.language', app()->isLocale('ur') ? 'en' : 'ur') }}">
                        {{ app()->isLocale('ur') ? 'English' : 'اردو' }}
                    </a>
                    <a href="{{ route('website.contact') }}">{{ app()->isLocale('ur') ? 'بکنگ کے لیے رابطہ' : 'Contact for Booking' }}</a>
                </div>
            </section>
            <section>
                <h3>{{ app()->isLocale('ur') ? 'پالیسیاں' : 'Policies' }}</h3>
                <div>
                    <a href="{{ route('website.legal.terms', $homeV3LegalParameters) }}">{{ __('premium-studio.terms') }}</a>
                    <a href="{{ route('website.legal.privacy', $homeV3LegalParameters) }}">{{ __('premium-studio.privacy') }}</a>
                    <a href="{{ route('website.legal.refund', $homeV3LegalParameters) }}">{{ __('premium-studio.refund') }}</a>
                    <a href="{{ route('website.legal.cancellation', $homeV3LegalParameters) }}">{{ __('premium-studio.cancellation') }}</a>
                    <a href="{{ route('website.legal.disclaimer', $homeV3LegalParameters) }}">{{ __('premium-studio.disclaimer') }}</a>
                </div>
            </section>
        </div>
        {{-- KES V3 COMPLETE FOOTER DIRECTORY END --}}

        <div class="shell footer__bottom">
            <span>
                © {{ now()->year }}
                {{ $company?->company_name ?: 'Karwan E Al Saeed Travel & Tours' }}
            </span>

            <span>
                {{ __('premium-studio.footer_note') }}
            </span>
        </div>
    </footer>

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

    <script
        src="{{ asset('js/karwan-premium-studio-v3.js') }}?v={{ file_exists(public_path('js/karwan-premium-studio-v3.js')) ? filemtime(public_path('js/karwan-premium-studio-v3.js')) : time() }}"
        defer
    ></script>
</body>
</html>
