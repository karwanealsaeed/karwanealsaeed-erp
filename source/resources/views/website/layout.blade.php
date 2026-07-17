<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Karwan E Al Saeed Travel & Tours' }}</title>
    <meta name="description" content="{{ $description ?? 'Karwan E Al Saeed Travel & Tours' }}">
    <link rel="stylesheet" href="{{ asset('css/website-theme.css') }}?v={{ file_exists(public_path('css/website-theme.css')) ? filemtime(public_path('css/website-theme.css')) : time() }}">
<link rel="stylesheet" href="{{ asset('css/karwan-ai.css') }}?v={{ file_exists(public_path('css/karwan-ai.css')) ? filemtime(public_path('css/karwan-ai.css')) : time() }}">
</head>
<body class="kes-public-site">
    <header class="kes-site-header">
        <div class="kes-topbar">
            <div class="kes-topbar-inner">
                <div class="kes-topbar-left">
                    <span class="kes-topbar-label">Office:</span>
                    <span>{{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}</span>
                </div>

                <div class="kes-topbar-right">
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->mobile ?? '+923012592342') }}">
                        <span class="kes-topbar-label">Call:</span>
                        {{ $company->mobile ?? '+92 301 2592342' }}
                    </a>

                    <span class="kes-topbar-divider"></span>

                    <a href="mailto:{{ $company->email ?? 'karwanealsaeed@gmail.com' }}">
                        <span class="kes-topbar-label">Email:</span>
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
                            Umrah &bull; Visas &bull; Tickets &bull; Tours
                        </span>
                    </span>
                </a>

                <div class="kes-nav-links kes-desktop-nav">
                    <a href="{{ route('website.home') }}"
                       class="{{ request()->routeIs('website.home') ? 'is-active' : '' }}">
                        Home
                    </a>

                    <a href="{{ route('website.about') }}"
                       class="{{ request()->routeIs('website.about') ? 'is-active' : '' }}">
                        About
                    </a>

                    <a href="{{ route('website.services') }}"
                       class="{{ request()->routeIs('website.services') ? 'is-active' : '' }}">
                        Services
                    </a>

                    <a href="{{ route('website.umrah') }}"
                       class="{{ request()->routeIs('website.umrah*') ? 'is-active' : '' }}">
                        Umrah
                    </a>

                    <a href="{{ route('website.visas') }}"
                       class="{{ request()->routeIs('website.visas') ? 'is-active' : '' }}">
                        Visas
                    </a>

                    <a href="{{ route('website.tickets') }}"
                       class="{{ request()->routeIs('website.tickets') ? 'is-active' : '' }}">
                        Tickets
                    </a>

                    <a href="{{ route('website.hotels') }}"
                       class="{{ request()->routeIs('website.hotels') ? 'is-active' : '' }}">
                        Hotels
                    </a>

                    <a href="{{ route('website.tours') }}"
                       class="{{ request()->routeIs('website.tours') ? 'is-active' : '' }}">
                        Tours
                    </a>

                    <a href="{{ route('website.contact') }}"
                       class="{{ request()->routeIs('website.contact') ? 'is-active' : '' }}">
                        Contact
                    </a>

                    <details class="kes-login-menu">
                        <summary>Login</summary>

                        <div class="kes-login-dropdown">
                            <a href="{{ route('customer.portal.login') }}">Customer Login</a>
                            <a href="/admin">Admin Login</a>
                        </div>
                    </details>
                </div>

                <details class="kes-mobile-nav">
                    <summary>
                        <span>Menu</span>
                        <span class="kes-menu-lines" aria-hidden="true"></span>
                    </summary>

                    <div class="kes-mobile-nav-panel">
                        <a href="{{ route('website.home') }}">Home</a>
                        <a href="{{ route('website.about') }}">About Us</a>
                        <a href="{{ route('website.services') }}">Our Services</a>
                        <a href="{{ route('website.umrah') }}">Umrah Packages</a>
                        <a href="{{ route('website.visas') }}">Visit Visas</a>
                        <a href="{{ route('website.tickets') }}">Air Ticketing</a>
                        <a href="{{ route('website.hotels') }}">Hotel Booking</a>
                        <a href="{{ route('website.tours') }}">Pakistan Tours</a>
                        <a href="{{ route('website.contact') }}">Contact Us</a>

                        <div class="kes-mobile-login-links">
                            <a href="{{ route('customer.portal.login') }}">Customer Login</a>
                            <a href="/admin">Admin Login</a>
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

                            <small>Your Trusted Travel Partner</small>
                        </span>
                    </a>

                    <p>
                        Professional services for Umrah packages, visit visas,
                        air ticketing, hotel booking and Pakistan tours.
                    </p>

                    <a class="kes-footer-whatsapp"
                       href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
                       target="_blank"
                       rel="noopener">
                        Chat With Us on WhatsApp
                    </a>
                </div>

                <div class="kes-footer-column">
                    <h3>Quick Links</h3>

                    <a href="{{ route('website.home') }}">Home</a>
                    <a href="{{ route('website.about') }}">About Us</a>
                    <a href="{{ route('website.services') }}">Our Services</a>
                    <a href="{{ route('website.contact') }}">Contact Us</a>
                    <a href="{{ route('customer.portal.login') }}">Customer Portal</a>
                </div>

                <div class="kes-footer-column">
                    <h3>Our Services</h3>

                    <a href="{{ route('website.umrah') }}">Umrah Packages</a>
                    <a href="{{ route('website.visas') }}">Visit Visas</a>
                    <a href="{{ route('website.tickets') }}">Air Ticketing</a>
                    <a href="{{ route('website.hotels') }}">Hotel Booking</a>
                    <a href="{{ route('website.tours') }}">Pakistan Tours</a>
                </div>

                <div class="kes-footer-column kes-footer-contact">
                    <h3>Contact Information</h3>

                    <div>
                        <span>Office</span>
                        <p>
                            {{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}
                        </p>
                    </div>

                    <div>
                        <span>Phone</span>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->mobile ?? '+923012592342') }}">
                            {{ $company->mobile ?? '+92 301 2592342' }}
                        </a>
                    </div>

                    <div>
                        <span>Email</span>
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
                    All rights reserved.
                </p>

                <p>
                    Umrah &bull; Visas &bull; Tickets &bull; Hotels &bull; Tours
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
        <summary>💬 Support</summary>

        <div class="kes-support-panel">
            <div class="kes-support-head">
                <strong>How can we help?</strong>
                <span>Karwan E Al Saeed Support</span>
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

                <input type="text" name="name" value="{{ old('name') }}" placeholder="Your Name" required>
                <input type="text" name="mobile" value="{{ old('mobile') }}" placeholder="Mobile / WhatsApp" required>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email optional">

                <select name="service_type">
                    <option value="">Select Service</option>
                    <option value="Umrah Package" @selected(old('service_type') === 'Umrah Package')>Umrah Package</option>
                    <option value="Visit Visa" @selected(old('service_type') === 'Visit Visa')>Visit Visa</option>
                    <option value="Air Ticketing" @selected(old('service_type') === 'Air Ticketing')>Air Ticketing</option>
                    <option value="Hotel Booking" @selected(old('service_type') === 'Hotel Booking')>Hotel Booking</option>
                    <option value="Travel Insurance" @selected(old('service_type') === 'Travel Insurance')>Travel Insurance</option>
                    <option value="Pakistan Tours" @selected(old('service_type') === 'Pakistan Tours')>Pakistan Tours</option>
                </select>

                <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Subject optional">

                <textarea name="message" rows="4" placeholder="Write your message..." required>{{ old('message') }}</textarea>

                <button type="submit">Submit Inquiry</button>
            </form>

            <a class="kes-support-whatsapp"
               href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?? '+923012592342') }}"
               target="_blank">
                WhatsApp Now
            </a>
        </div>
    </details>
</div>
@include('components.karwan-ai-widget')
</body>
</html>