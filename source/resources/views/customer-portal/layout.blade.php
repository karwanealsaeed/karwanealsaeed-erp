<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->isLocale('ur') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>Agent Portal | Karwan E Al Saeed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/customer-portal.css') }}?v={{ file_exists(public_path('css/customer-portal.css')) ? filemtime(public_path('css/customer-portal.css')) : time() }}">
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
    {{-- PHASE 16E-2A AGENT PORTAL SHARED SHELL START --}}
    <link rel="stylesheet" href="{{ asset('css/agent-portal-v3.css') }}">
{{-- PHASE 16E-3F4 SHARED PUBLIC V3 PORTAL FOUNDATION START --}}
<link
    rel="stylesheet"
    href="{{ asset('css/karwan-portal-premium-studio-v3.css') }}?v={{ filemtime(public_path('css/karwan-portal-premium-studio-v3.css')) }}"
>
{{-- PHASE 16E-3F4 SHARED PUBLIC V3 PORTAL FOUNDATION END --}}
    {{-- PHASE 16E-2A AGENT PORTAL SHARED SHELL END --}}
</head>
<body class="cp-body kes-agent-portal" data-kes-agent-portal="v3">
    <a class="kes-agent-skip-link" href="#agent-portal-main">{{ app()->isLocale('ur') ? 'مرکزی مواد پر جائیں' : 'Skip to main content' }}</a>
    <div class="cp-mobile-top">
        <button
            id="customerPortalMenuButton"
            type="button"
            aria-label="Open agent portal navigation"
         data-agent-nav-toggle aria-controls="customerPortalSidebar" aria-expanded="false">☰</button>
        <strong>Agent Portal</strong>
        <a href="{{ route('website.home') }}">Website</a>
    </div>

    <div class="cp-app">
        <aside class="cp-sidebar" id="customerPortalSidebar">
            <div class="cp-sidebar-brand">
                <div class="cp-sidebar-logo">K</div>
                <div>
                    <h2>Karwan E Al Saeed</h2>
                    <p>Agent Portal</p>
                </div>
            </div>

            <div class="cp-sidebar-user">
                <span>Welcome</span>
                <strong>{{ session('customer_portal_name', 'Customer') }}</strong>
            </div>

            <nav id="agent-portal-navigation" class="cp-nav" aria-label="Agent portal navigation" data-agent-nav>
                <a href="{{ route('customer.portal.dashboard') }}" class="{{ request()->routeIs('customer.portal.dashboard') ? 'active' : '' }}">
                    <span>🏠</span> Dashboard
                </a>

                <a href="{{ route('customer.portal.ledger') }}" class="{{ request()->routeIs('customer.portal.ledger') ? 'active' : '' }}">
                    <span>💰</span> Ledger / Balance
                </a>

                <a href="{{ route('customer.portal.tracking') }}" class="{{ request()->routeIs('customer.portal.tracking') ? 'active' : '' }}">
                    <span>📍</span> Booking Tracking
                </a>

                <a href="{{ route('customer.portal.visa') }}" class="{{ request()->routeIs('customer.portal.visa') ? 'active' : '' }}">
                    <span>🛂</span> Visa Applications
                </a>

                <a href="{{ route('customer.portal.flights') }}" class="{{ request()->routeIs('customer.portal.flights') ? 'active' : '' }}">
                    <span>✈️</span> Flight Bookings
                </a>

                <a href="{{ route('customer.portal.umrah') }}" class="{{ request()->routeIs('customer.portal.umrah') ? 'active' : '' }}">
                    <span>🕋</span> Umrah Bookings
                </a>

                <a href="{{ route('customer.portal.hotels') }}" class="{{ request()->routeIs('customer.portal.hotels') ? 'active' : '' }}">
                    <span>🏨</span> Hotel Bookings
                </a>

                <a href="{{ route('customer.portal.travel_insurance') }}" class="{{ request()->routeIs('customer.portal.travel_insurance*') ? 'active' : '' }}">
                    <span>🛡️</span> Travel Insurance
                </a>

                <a href="{{ route('customer.portal.invoices') }}" class="{{ request()->routeIs('customer.portal.invoices') ? 'active' : '' }}">
                    <span>🧾</span> Invoices
                </a>

                <a href="{{ route('customer.portal.vouchers') }}" class="{{ request()->routeIs('customer.portal.vouchers') ? 'active' : '' }}">
                    <span>📄</span> Vouchers
                </a>

                <a href="{{ route('customer.portal.documents') }}" class="{{ request()->routeIs('customer.portal.documents') ? 'active' : '' }}">
                    <span>📁</span> Documents
                </a>

                <a href="{{ route('customer.portal.payment_uploads') }}" class="{{ request()->routeIs('customer.portal.payment_uploads') ? 'active' : '' }}">
                    <span>🏦</span> Payment Upload
                </a>

                <a href="{{ route('customer.portal.family') }}" class="{{ request()->routeIs('customer.portal.family') ? 'active' : '' }}">
                    <span>👨‍👩‍👧</span> Family Members
                </a>

                <a href="{{ route('customer.portal.promotions') }}" class="{{ request()->routeIs('customer.portal.promotions') ? 'active' : '' }}">Latest Offers</a>
                <a href="{{ route('customer.portal.notifications') }}" class="{{ request()->routeIs('customer.portal.notifications') ? 'active' : '' }}">
                    <span>🔔</span> Notifications & Offers
                </a>

                <a href="{{ route('customer.portal.support') }}" class="{{ request()->routeIs('customer.portal.support*') ? 'active' : '' }}">
                    <span>💬</span> Support Tickets
                </a>

                <a href="{{ route('customer.portal.profile') }}" class="{{ request()->routeIs('customer.portal.profile') ? 'active' : '' }}">
                    <span>👤</span> Profile
                </a>
            </nav>

            <form method="POST" action="{{ route('customer.portal.logout') }}" class="cp-logout" id="customerPortalLogoutForm">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </aside>

        <main class="cp-main" id="agent-portal-main" tabindex="-1">
            <div class="cp-topbar">
                <div>
                    <span>Karwan E Al Saeed Travel & Tours</span>
                    <strong>Agent Dashboard</strong>
                </div>

                <div class="cp-topbar-actions">
                    <a href="{{ route('website.home') }}">Public Website</a>
                    <a href="https://wa.me/923012592342" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                </div>
            </div>

            <section class="cp-content">
                @yield('content')
            </section>
        </main>
    </div>

    <button class="cp-backdrop kes-agent-nav-backdrop" type="button" data-agent-nav-close aria-label="Close agent portal navigation" tabindex="-1"></button>

    <script>
        let customerPortalTimer;

        function resetCustomerPortalTimer() {
            clearTimeout(customerPortalTimer);

            customerPortalTimer = setTimeout(function () {
                const form = document.getElementById('customerPortalLogoutForm');
                if (form) {
                    form.submit();
                }
            }, 30 * 60 * 1000);
        }

        ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(function (eventName) {
            document.addEventListener(eventName, resetCustomerPortalTimer, { passive: true });
        });

        resetCustomerPortalTimer();
    </script>
@include('components.karwan-ai-widget')
    {{-- KES PREMIUM UI SCRIPT START --}}
    <script src="{{ asset('js/karwan-premium-ui.js') }}" defer></script>
    {{-- KES PREMIUM UI SCRIPT END --}}

    <script defer src="{{ asset('js/agent-portal-v3.js') }}"></script>
</body>
</html>
