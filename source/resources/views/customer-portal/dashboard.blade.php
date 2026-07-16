@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Welcome, {{ $customer->name }}</h1>
            <p>Agent portal me sirf aap ka balance aur aap ki purchased services show hoti hain.</p>
        </div>

        <button onclick="window.print()">Print Summary</button>
    </div>

    @if (session('success'))
        <div class="cp-alert success">{{ session('success') }}</div>
    @endif

    <div class="cp-stats-grid">
        <div class="cp-stat-card">
            <span>Your Balance</span>
            @php
                $balance = (float) ($ledgerBalance ?? 0);
            @endphp
            <strong>
                PKR {{ number_format(abs($balance), 2) }}
                {{ $balance > 0 ? 'Dr' : ($balance < 0 ? 'Cr' : '') }}
            </strong>
        </div>

        <div class="cp-stat-card">
            <span>Unread Notifications</span>
            <strong>{{ $notificationsCount ?? 0 }}</strong>
        </div>

        <div class="cp-stat-card">
            <span>Your Support Tickets</span>
            <strong>{{ $supportTicketsCount ?? 0 }}</strong>
        </div>

        <div class="cp-stat-card">
            <span>Your Payment Uploads</span>
            <strong>{{ $paymentUploadsCount ?? 0 }}</strong>
        </div>
    </div>

    <div class="cp-card-grid">
        <div class="cp-info-card">
            <span>Finance</span>
            <h3>Your Ledger / Balance</h3>
            <p>Sirf aap ka debit, credit aur closing balance show hoga.</p>
            <a href="{{ route('customer.portal.ledger') }}">View Ledger</a>
        </div>

        <div class="cp-info-card">
            <span>Bookings</span>
            <h3>Your Booking Tracking</h3>
            <p>Sirf aap ki visa, flight, Umrah aur hotel bookings show hongi.</p>
            <a href="{{ route('customer.portal.tracking') }}">Open Tracking</a>
        </div>

        <div class="cp-info-card">
            <span>Invoices</span>
            <h3>Your Invoices</h3>
            <p>Aap ke naam par bani invoices aur amounts check karein.</p>
            <a href="{{ route('customer.portal.invoices') }}">View Invoices</a>
        </div>

        <div class="cp-info-card">
            <span>Vouchers</span>
            <h3>Your Vouchers</h3>
            <p>Aap ke Umrah / hotel vouchers yahan show honge.</p>
            <a href="{{ route('customer.portal.vouchers') }}">View Vouchers</a>
        </div>

        <div class="cp-info-card">
            <span>Payments</span>
            <h3>Upload Payment Proof</h3>
            <p>Bank transfer, deposit, JazzCash/EasyPaisa proof upload karein.</p>
            <a href="{{ route('customer.portal.payment_uploads') }}">Upload Payment</a>
        </div>

        <div class="cp-info-card">
            <span>Support</span>
            <h3>Support Tickets</h3>
            <p>Apni booking/payment issue ke liye support ticket create karein.</p>
            <a href="{{ route('customer.portal.support') }}">Open Support</a>
        </div>
    </div>
@endsection