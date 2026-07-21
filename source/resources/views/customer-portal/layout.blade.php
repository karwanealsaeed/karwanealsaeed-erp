<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Portal | Karwan E Al Saeed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/customer-portal.css') }}?v={{ file_exists(public_path('css/customer-portal.css')) ? filemtime(public_path('css/customer-portal.css')) : time() }}">
<link rel="stylesheet" href="{{ asset('css/karwan-ai.css') }}?v={{ file_exists(public_path('css/karwan-ai.css')) ? filemtime(public_path('css/karwan-ai.css')) : time() }}">
</head>
<body class="cp-body">
    <div class="cp-mobile-top">
        <button
            id="customerPortalMenuButton"
            type="button"
            aria-label="Open customer portal navigation"
            aria-controls="customerPortalSidebar"
            aria-expanded="false"
            onclick="document.body.classList.toggle('cp-menu-open'); this.setAttribute('aria-expanded', document.body.classList.contains('cp-menu-open') ? 'true' : 'false');"
        >☰</button>
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

            <nav class="cp-nav" aria-label="Customer portal navigation">
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

        <main class="cp-main">
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

    <div class="cp-backdrop" onclick="document.body.classList.remove('cp-menu-open'); document.getElementById('customerPortalMenuButton')?.setAttribute('aria-expanded', 'false')"></div>

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
</body>
</html>