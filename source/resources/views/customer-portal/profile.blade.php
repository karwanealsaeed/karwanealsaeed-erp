@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>My Profile</h1>
            <p>Update contact information and secure your portal password.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="cp-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="cp-alert">
            <strong>Please correct the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cp-form-card">
        <h2 style="margin-top: 0;">Contact Information</h2>

        <form
            method="POST"
            action="{{ route('customer.portal.profile.update') }}"
        >
            @csrf
            @method('PATCH')

            <div class="cp-form-grid">
                <div>
                    <label for="profile-name">Name</label>

                    <input
                        id="profile-name"
                        type="text"
                        name="name"
                        value="{{ old('name', $customer->name) }}"
                        maxlength="255"
                        required
                    >
                </div>

                <div>
                    <label for="profile-father-name">Father Name</label>

                    <input
                        id="profile-father-name"
                        type="text"
                        name="father_name"
                        value="{{ old('father_name', $customer->father_name) }}"
                        maxlength="255"
                    >
                </div>

                <div>
                    <label for="profile-mobile">Mobile</label>

                    <input
                        id="profile-mobile"
                        type="text"
                        name="mobile"
                        value="{{ old('mobile', $customer->mobile) }}"
                        maxlength="50"
                        required
                    >
                </div>

                <div>
                    <label for="profile-whatsapp">WhatsApp</label>

                    <input
                        id="profile-whatsapp"
                        type="text"
                        name="whatsapp"
                        value="{{ old('whatsapp', $customer->whatsapp) }}"
                        maxlength="50"
                    >
                </div>

                <div>
                    <label for="profile-email">Email</label>

                    <input
                        id="profile-email"
                        type="email"
                        name="email"
                        value="{{ old('email', $customer->email) }}"
                        maxlength="255"
                    >
                </div>

                <div>
                    <label for="profile-date-of-birth">Date of Birth</label>

                    <input
                        id="profile-date-of-birth"
                        type="date"
                        name="date_of_birth"
                        value="{{ old('date_of_birth', $customer->date_of_birth?->format('Y-m-d')) }}"
                    >
                </div>

                <div>
                    <label for="profile-city">City</label>

                    <input
                        id="profile-city"
                        type="text"
                        name="city"
                        value="{{ old('city', $customer->city) }}"
                        maxlength="255"
                    >
                </div>

                <div>
                    <label for="profile-country">Country</label>

                    <input
                        id="profile-country"
                        type="text"
                        name="country"
                        value="{{ old('country', $customer->country) }}"
                        maxlength="255"
                    >
                </div>
            </div>

            <div style="margin-top: 16px;">
                <label for="profile-address">Address</label>

                <textarea
                    id="profile-address"
                    name="address"
                    rows="4"
                    maxlength="2000"
                >{{ old('address', $customer->address) }}</textarea>
            </div>

            <button type="submit">
                Save Profile
            </button>
        </form>
    </div>

    <div class="cp-card">
        <h2 style="margin-top: 0;">Verified Identity Details</h2>

        <div class="cp-table-wrap">
            <table class="cp-table">
                <tbody>
                    <tr>
                        <th scope="row">CNIC</th>
                        <td>{{ $customer->cnic ?: '-' }}</td>
                    </tr>

                    <tr>
                        <th scope="row">Passport No</th>
                        <td>{{ $customer->passport_no ?: '-' }}</td>
                    </tr>

                    <tr>
                        <th scope="row">Account Type</th>
                        <td>{{ $customer->customer_type ?: '-' }}</td>
                    </tr>

                    <tr>
                        <th scope="row">Status</th>
                        <td>{{ $customer->status ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p>
            Identity details change karwane ke liye office se contact karein.
        </p>
    </div>

    <div class="cp-form-card">
        <h2 style="margin-top: 0;">Change Portal Password</h2>

        <form
            method="POST"
            action="{{ route('customer.portal.profile.password') }}"
        >
            @csrf
            @method('PUT')

            <div class="cp-form-grid">
                <div>
                    <label for="current-password">Current Password</label>

                    <input
                        id="current-password"
                        type="password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div>
                    <label for="new-password">New Password</label>

                    <input
                        id="new-password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>

                <div>
                    <label for="password-confirmation">Confirm New Password</label>

                    <input
                        id="password-confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>
            </div>

            <p>
                Password kam az kam 8 characters ka ho aur usmein letters aur numbers hon.
            </p>

            <button type="submit">
                Change Password
            </button>
        </form>
    </div>
@endsection