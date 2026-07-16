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
<body>
    <div class="kes-topbar">
        <div class="kes-topbar-inner">
            <div class="kes-topbar-left">
                <span>📍 {{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}</span>
            </div>

            <div class="kes-topbar-right">
                <span>📞 {{ $company->mobile ?? '+92 301 2592342' }}</span>
                <span>|</span>
                <span>✉ {{ $company->email ?? 'karwanealsaeed@gmail.com' }}</span>
            </div>
        </div>
    </div>

    <nav class="kes-navbar">
        <div class="kes-navbar-inner">
            <a href="{{ route('website.home') }}" class="kes-brand">
                @if (file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Karwan E Al Saeed" class="kes-logo-img">
                @else
                    <span class="kes-logo-fallback">KES</span>
                @endif

                <span>
                    <span class="kes-brand-title">
                        {{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}
                    </span>

                    <span class="kes-brand-subtitle">
                        Umrah • Visas • Tickets • Tours
                    </span>
                </span>
            </a>

            <div class="kes-nav-links">
                <a href="{{ route('website.home') }}">Home</a>
                <a href="{{ route('website.about') }}">About</a>
                <a href="{{ route('website.services') }}">Services</a>
                <a href="{{ route('website.umrah') }}">Umrah</a>
                <a href="{{ route('website.visas') }}">Visas</a>
                <a href="{{ route('website.tickets') }}">Tickets</a>
                <a href="{{ route('website.hotels') }}">Hotels</a>
                <a href="{{ route('website.tours') }}">Tours</a>
<a href="{{ route('website.contact') }}">Contact</a>

<details class="kes-login-menu">
    <summary>Login</summary>

    <div class="kes-login-dropdown">
        <a href="{{ route('customer.portal.login') }}">👤 Customer Login</a>
        <a href="/admin">🔐 Admin</a>
    </div>
</details>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="kes-footer">
        <div class="kes-footer-grid">
            <div>
                <h3>{{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}</h3>
                <p>Your trusted travel partner for Umrah, Visas, Air Tickets, Hotels and Tours.</p>
            </div>

            <div>
                <h3>Quick Links</h3>
                <a href="{{ route('website.about') }}">About Us</a>
                <a href="{{ route('website.services') }}">Our Services</a>
                <a href="{{ route('website.umrah') }}">Umrah Packages</a>
                <a href="{{ route('website.contact') }}">Contact</a>

<details class="kes-login-menu">
    <summary>Login</summary>

    <div class="kes-login-dropdown">
        <a href="{{ route('customer.portal.login') }}">👤 Customer Login</a>
        <a href="/admin">🔐 Admin</a>
    </div>
</details>
            </div>

            <div>
                <h3>Contact</h3>
                <p>{{ $company->mobile ?? '+92 301 2592342' }}</p>
                <p>{{ $company->whatsapp ?? '+92 301 2592342' }}</p>
                <p>{{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}</p>
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