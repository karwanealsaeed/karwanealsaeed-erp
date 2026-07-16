<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Portal Login | Karwan E Al Saeed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/customer-portal.css') }}?v={{ file_exists(public_path('css/customer-portal.css')) ? filemtime(public_path('css/customer-portal.css')) : time() }}">
</head>
<body class="cp-auth-body">
    <div class="cp-auth-shell">
        <div class="cp-auth-brand">
            <div class="cp-auth-logo">K</div>
            <h1>Karwan E Al Saeed</h1>
            <p>B2B Agent Portal for balance, ledger, bookings, invoices, vouchers and support.</p>

            <div class="cp-auth-points">
                <span>✓ Agent Balance</span>
                <span>✓ Own Bookings</span>
                <span>✓ Invoices & Vouchers</span>
                <span>✓ Payment Upload</span>
            </div>
        </div>

        <div class="cp-auth-card">
            <div class="cp-auth-head">
                <span>Agent Portal</span>
                <h2>Agent Login</h2>
                <p>Login to view your own balance, purchased services, invoices, vouchers and payment history.</p>
            </div>

            @if (session('error'))
                <div class="cp-alert error">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="cp-alert success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="cp-alert error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('customer.portal.authenticate') }}" class="cp-auth-form">
                @csrf

                <label>
                    <span>Mobile / Email / CNIC / Passport</span>
                    <input type="text" name="login" value="{{ old('login') }}" placeholder="Enter mobile, email, CNIC or passport" required autofocus>
                </label>

                <label>
                    <span>Password</span>
                    <input type="password" name="password" placeholder="Enter agent portal password" required>
                </label>

                <button type="submit">Login</button>
            </form>

            <div class="cp-auth-links">
                <a href="https://wa.me/923012592342" target="_blank">Request Agent Login</a>
                <a href="{{ route('website.home') }}">Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>