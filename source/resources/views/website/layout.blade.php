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
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?: ($company->mobile ?? '+923012592342')) }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp: {{ $company->mobile ?? '+92 301 2592342' }}">
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
                        <a href="{{ route('website.insurance') }}">{{ __('website.travel_insurance') }}</a>
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
                    <a href="{{ route('website.faq') }}" class="{{ request()->routeIs('website.faq*') ? 'is-active' : '' }}">{{ __('faq.title') }}</a>
                    <a href="{{ route('website.blog') }}" class="{{ request()->routeIs('website.blog*') ? 'is-active' : '' }}">{{ __('blog.title') }}</a>
                    <a href="{{ route('website.testimonials') }}" class="{{ request()->routeIs('website.testimonials*') ? 'is-active' : '' }}">{{ __('testimonials.title') }}</a>
                    <a href="{{ route('website.contact') }}">{{ __('website.contact_us') }}</a>
                    <a href="{{ route('customer.portal.login') }}">{{ __('website.customer_portal') }}</a>
                </div>

                <div class="kes-footer-column">
                    <h3>{{ __('website.our_services') }}</h3>

                    <a href="{{ route('website.umrah') }}">{{ __('website.umrah_packages') }}</a>
                    <a href="{{ route('website.visas') }}">{{ __('website.visit_visas') }}</a>
                    <a href="{{ route('website.tickets') }}">{{ __('website.air_ticketing') }}</a>
                    <a href="{{ route('website.hotels') }}">{{ __('website.hotel_booking') }}</a>
                    <a href="{{ route('website.insurance') }}">{{ __('website.travel_insurance') }}</a>
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
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?: ($company->mobile ?? '+923012592342')) }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp: {{ $company->mobile ?? '+92 301 2592342' }}">
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

                @php
                    $footerLegalParameters = app()->isLocale('ur')
                        ? ['lang' => 'ur']
                        : [];
                @endphp

                <nav
                    class="kes-footer-legal"
                    aria-label="{{ __('legal.footer_label') }}"
                >
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
                </nav>
            </div>
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
</body>
</html>
