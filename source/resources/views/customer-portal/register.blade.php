<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Register | Karwan E Al Saeed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/customer-portal.css') }}">
</head>
<body class="cp-auth-body">
    <div class="cp-auth-shell">
        <div class="cp-auth-brand">
            <div class="cp-auth-logo">K</div>
            <h1>Karwan E Al Saeed</h1>
            <p>Create your customer account and manage your travel records online.</p>

            <div class="cp-auth-points">
                <span>✓ Umrah Bookings</span>
                <span>✓ Visa Tracking</span>
                <span>✓ Flight Records</span>
                <span>✓ Payment Upload</span>
            </div>
        </div>

        <div class="cp-auth-card wide">
            <div class="cp-auth-head">
                <span>Customer Portal</span>
                <h2>Create Account</h2>
                <p>Register to access your bookings, invoices, vouchers and documents.</p>
            </div>

            @if ($errors->any())
                <div class="cp-alert error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('customer.portal.register.store') }}" class="cp-auth-form">
                @csrf

                <div class="cp-auth-grid">
                    <label>
                        <span>Full Name</span>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter full name" required>
                    </label>

                    <label>
                        <span>Mobile / WhatsApp</span>
                        <input type="text" name="mobile" value="{{ old('mobile') }}" placeholder="03XXXXXXXXX" required>
                    </label>

                    <label>
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email optional">
                    </label>

                    <label>
                        <span>CNIC</span>
                        <input type="text" name="cnic" value="{{ old('cnic') }}" placeholder="CNIC optional">
                    </label>

                    <label>
                        <span>Passport No</span>
                        <input type="text" name="passport_no" value="{{ old('passport_no') }}" placeholder="Passport optional">
                    </label>

                    <label>
                        <span>Password</span>
                        <input type="password" name="password" placeholder="Minimum 6 characters" required>
                    </label>

                    <label class="full">
                        <span>Confirm Password</span>
                        <input type="password" name="password_confirmation" placeholder="Confirm password" required>
                    </label>
                </div>

                <button type="submit">Create Account</button>
            </form>

            <div class="cp-auth-links">
                <a href="{{ route('customer.portal.login') }}">Already registered? Login</a>
                <a href="{{ route('website.home') }}">Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>