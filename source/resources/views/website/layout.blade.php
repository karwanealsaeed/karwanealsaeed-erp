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
<body class="kes-public-site kes-locale-{{ app()->getLocale() }} {{ request()->routeIs('website.home') ? 'page-home' : 'page-inner' }}">
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

    <a class="skip-link" href="#main-content">
        {{ app()->isLocale('ur') ? 'مواد پر جائیں' : 'Skip to content' }}
    </a>

    <header class="site-header" data-header>
        <div class="utility-bar">
            <div class="shell utility-bar__inner">
                <span>
                    {{ $company?->address ?? 'Office # 90,91 College Road, Chichawatni' }}
                </span>

                <div>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company?->mobile ?? '+923012592342') }}">
                        {{ $company?->mobile ?? '+92 301 2592342' }}
                    </a>
                    <span class="utility-dot" aria-hidden="true"></span>
                    <a href="mailto:{{ $company?->email ?? 'karwanealsaeed@gmail.com' }}">
                        {{ $company?->email ?? 'karwanealsaeed@gmail.com' }}
                    </a>
                </div>
            </div>
        </div>

        <nav class="nav" aria-label="{{ __('website.menu') }}">
            <div class="shell nav__inner">
                <a
                    class="brand magnetic"
                    href="{{ route('website.home') }}"
                    aria-label="{{ $company?->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}"
                >
                    <span class="brand__mark">
                        <img src="{{ $businessLogo }}" alt="">
                        <b>KES</b>
                    </span>

                    <span class="brand__copy">
                        <strong>
                            {{ $company?->company_name ?? 'Karwan E Al Saeed' }}
                        </strong>
                        <small>{{ __('website.brand_services') }}</small>
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
                    <b>{{ __('website.menu') }}</b>
                </button>

                <div class="nav__menu" id="main-menu" data-menu>
                    <a
                        href="{{ route('website.home') }}"
                        class="{{ request()->routeIs('website.home') ? 'is-active' : '' }}"
                    >
                        {{ __('website.home') }}
                    </a>
                    <a
                        href="{{ route('website.umrah') }}"
                        class="{{ request()->routeIs('website.umrah*') ? 'is-active' : '' }}"
                    >
                        {{ __('website.umrah') }}
                    </a>
                    <a
                        href="{{ route('website.services') }}"
                        class="{{ request()->routeIs('website.services') ? 'is-active' : '' }}"
                    >
                        {{ __('website.services') }}
                    </a>
                    <a
                        href="{{ route('website.visas') }}"
                        class="{{ request()->routeIs('website.visas') ? 'is-active' : '' }}"
                    >
                        {{ __('website.visas') }}
                    </a>
                    <a
                        href="{{ route('website.tickets') }}"
                        class="{{ request()->routeIs('website.tickets') ? 'is-active' : '' }}"
                    >
                        {{ __('website.tickets') }}
                    </a>
                    <a
                        href="{{ route('website.insurance') }}"
                        class="{{ request()->routeIs('website.insurance') ? 'is-active' : '' }}"
                    >
                        {{ __('website.travel_insurance') }}
                    </a>
                    <a
                        href="{{ route('website.tours') }}"
                        class="{{ request()->routeIs('website.tours*') ? 'is-active' : '' }}"
                    >
                        {{ __('website.tours') }}
                    </a>
                    <a
                        href="{{ route('website.contact') }}"
                        class="{{ request()->routeIs('website.contact') ? 'is-active' : '' }}"
                    >
                        {{ __('website.contact_us') }}
                    </a>

                    <div
                        class="language-switch"
                        aria-label="{{ __('website.language') }}"
                    >
                        <a
                            href="{{ route('website.language', 'en') }}"
                            class="{{ app()->isLocale('en') ? 'is-active' : '' }}"
                        >
                            EN
                        </a>
                        <span>/</span>
                        <a
                            href="{{ route('website.language', 'ur') }}"
                            class="{{ app()->isLocale('ur') ? 'is-active' : '' }}"
                        >
                            اردو
                        </a>
                    </div>

                    <a
                        class="btn btn--gold magnetic agent-portal-cta"
                        href="{{ route('customer.portal.login') }}"
                    >
                        <span>{{ __('website.customer_portal') }}</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="shell footer__grid">
            <div class="footer__brand">
                <a class="brand" href="{{ route('website.home') }}">
                    <span class="brand__mark">
                        <img src="{{ $businessLogo }}" alt="">
                        <b>KES</b>
                    </span>
                    <span class="brand__copy">
                        <strong>
                            {{ $company?->company_name ?? 'Karwan E Al Saeed' }}
                        </strong>
                        <small>{{ __('website.trusted_partner') }}</small>
                    </span>
                </a>

                <p>{{ __('website.company_description') }}</p>

                <a
                    href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company?->whatsapp ?: ($company?->mobile ?? '+923012592342')) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {{ __('website.chat_whatsapp') }}
                </a>
            </div>

            <div>
                <h3>{{ __('website.quick_links') }}</h3>
                <a href="{{ route('website.about') }}">{{ __('website.about_us') }}</a>
                <a href="{{ route('website.gallery') }}">{{ __('gallery.title') }}</a>
                <a href="{{ route('website.promotions') }}">{{ __('promotions.title') }}</a>
                <a href="{{ route('website.blog') }}">{{ __('blog.title') }}</a>
                <a href="{{ route('website.faq') }}">{{ __('faq.title') }}</a>
            </div>

            <div>
                <h3>{{ __('website.our_services') }}</h3>
                <a href="{{ route('website.umrah') }}">{{ __('website.umrah_packages') }}</a>
                <a href="{{ route('website.visas') }}">{{ __('website.visit_visas') }}</a>
                <a href="{{ route('website.tickets') }}">{{ __('website.air_ticketing') }}</a>
                <a href="{{ route('website.hotels') }}">{{ __('website.hotel_booking') }}</a>
                <a href="{{ route('website.insurance') }}">{{ __('website.travel_insurance') }}</a>
                <a href="{{ route('website.tours') }}">{{ __('website.pakistan_tours') }}</a>
            </div>

            <div>
                <h3>{{ __('website.contact_information') }}</h3>
                <p>
                    {{ $company?->address ?? 'Office # 90,91 College Road, Chichawatni.' }}
                </p>
                <a
                    href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company?->whatsapp ?: ($company?->mobile ?? '+923012592342')) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {{ $company?->mobile ?? '+92 301 2592342' }}
                </a>
                <a href="mailto:{{ $company?->email ?? 'karwanealsaeed@gmail.com' }}">
                    {{ $company?->email ?? 'karwanealsaeed@gmail.com' }}
                </a>
                <a href="{{ route('customer.portal.login') }}">
                    {{ __('website.customer_portal') }}
                </a>
                <a href="/admin">{{ __('website.admin_login') }}</a>
            </div>
        </div>

        <div class="shell footer__bottom">
            <span>
                &copy; {{ date('Y') }}
                {{ $company?->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}.
                {{ __('website.copyright') }}
            </span>

            @php
                $footerLegalParameters = app()->isLocale('ur')
                    ? ['lang' => 'ur']
                    : [];
            @endphp

            <span class="footer__legal">
                <a href="{{ route('website.legal.privacy', $footerLegalParameters) }}">
                    {{ __('legal.links.privacy') }}
                </a>
                <a href="{{ route('website.legal.terms', $footerLegalParameters) }}">
                    {{ __('legal.links.terms') }}
                </a>
                <a href="{{ route('website.legal.refund', $footerLegalParameters) }}">
                    {{ __('legal.links.refund') }}
                </a>
                <a href="{{ route('website.legal.cancellation', $footerLegalParameters) }}">
                    {{ __('legal.links.cancellation') }}
                </a>
                <a href="{{ route('website.legal.disclaimer', $footerLegalParameters) }}">
                    {{ __('legal.links.disclaimer') }}
                </a>
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
        src="{{ asset('js/karwan-premium-ui.js') }}"
        defer
    ></script>
    <script
        src="{{ asset('js/karwan-premium-studio-v3.js') }}?v={{ file_exists(public_path('js/karwan-premium-studio-v3.js')) ? filemtime(public_path('js/karwan-premium-studio-v3.js')) : time() }}"
        defer
    ></script>
</body>
</html>
