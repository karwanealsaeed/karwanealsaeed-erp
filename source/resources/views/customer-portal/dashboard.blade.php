@extends('customer-portal.layout')

@section('content')
    @php
        $balance = (float) ($ledgerBalance ?? 0);
        $balanceDirection = $balance > 0 ? 'Dr' : ($balance < 0 ? 'Cr' : '');
    @endphp

    <div class="kes-agent-dashboard-v3">
        <section class="kes-agent-dashboard-hero" aria-labelledby="agent-dashboard-title">
            <div class="kes-agent-dashboard-hero__content">
                <span class="kes-agent-dashboard-eyebrow">Agent Workspace</span>

                <h1 id="agent-dashboard-title">
                    Welcome, <span>{{ $customer->name }}</span>
                </h1>

                <p>
                    Apna balance, bookings, invoices, documents aur support activity
                    ek secure dashboard se manage karein.
                </p>

                <div class="kes-agent-dashboard-hero__badges" aria-label="Portal access summary">
                    <span>
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="M12 3 4.5 6v5.8c0 4.6 3.1 7.9 7.5 9.2 4.4-1.3 7.5-4.6 7.5-9.2V6L12 3Zm0 4.2 4.2 1.7v2.9c0 2.9-1.7 5-4.2 6.1-2.5-1.1-4.2-3.2-4.2-6.1V8.9L12 7.2Z"/>
                        </svg>
                        Private account data
                    </span>

                    <span>
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="M12 2a7 7 0 0 0-7 7v3H3v10h18V12h-2V9a7 7 0 0 0-7-7Zm-4 10V9a4 4 0 1 1 8 0v3H8Zm4 3a2 2 0 0 1 1 3.7V20h-2v-1.3A2 2 0 0 1 12 15Z"/>
                        </svg>
                        Secure Agent Portal
                    </span>
                </div>
            </div>

            <button
                type="button"
                class="kes-agent-dashboard-print"
                onclick="window.print()"
            >
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M7 3h10v5H7V3Zm10 11H7v7h10v-7Zm2-7H5a3 3 0 0 0-3 3v6h3v-4h14v4h3v-6a3 3 0 0 0-3-3Z"/>
                </svg>
                <span>Print Summary</span>
            </button>
        </section>

        @if (session('success'))
            <div class="cp-alert success kes-agent-dashboard-alert" role="status">
                {{ session('success') }}
            </div>
        @endif

        <section class="kes-agent-dashboard-metrics" aria-label="Account overview">
            <article class="kes-agent-metric-card kes-agent-metric-card--balance">
                <div class="kes-agent-metric-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 5h18v14H3V5Zm3 3v2h12V8H6Zm0 5v3h5v-3H6Zm8 0v3h4v-3h-4Z"/>
                    </svg>
                </div>
                <div>
                    <span>Your Balance</span>
                    <strong>
                        PKR {{ number_format(abs($balance), 2) }}
                        {{ $balanceDirection }}
                    </strong>
                    <small>Current ledger position</small>
                </div>
            </article>

            <article class="kes-agent-metric-card">
                <div class="kes-agent-metric-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2a7 7 0 0 0-7 7v4l-2 3h18l-2-3V9a7 7 0 0 0-7-7Zm0 20a3 3 0 0 0 3-3H9a3 3 0 0 0 3 3Z"/>
                    </svg>
                </div>
                <div>
                    <span>Unread Notifications</span>
                    <strong>{{ $notificationsCount ?? 0 }}</strong>
                    <small>Updates requiring attention</small>
                </div>
            </article>

            <article class="kes-agent-metric-card">
                <div class="kes-agent-metric-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 3h16v13H8l-4 4V3Zm4 4v2h8V7H8Zm0 4v2h5v-2H8Z"/>
                    </svg>
                </div>
                <div>
                    <span>Your Support Tickets</span>
                    <strong>{{ $supportTicketsCount ?? 0 }}</strong>
                    <small>Open and recent requests</small>
                </div>
            </article>

            <article class="kes-agent-metric-card">
                <div class="kes-agent-metric-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 7 8h3v5h4V8h3l-5-5ZM5 15v5h14v-5h2v7H3v-7h2Z"/>
                    </svg>
                </div>
                <div>
                    <span>Your Payment Uploads</span>
                    <strong>{{ $paymentUploadsCount ?? 0 }}</strong>
                    <small>Submitted payment proofs</small>
                </div>
            </article>
        </section>

        <section class="kes-agent-dashboard-section" aria-labelledby="agent-services-title">
            <div class="kes-agent-dashboard-section__head">
                <div>
                    <span class="kes-agent-dashboard-eyebrow">Quick Actions</span>
                    <h2 id="agent-services-title">Manage your travel account</h2>
                </div>

                <p>
                    Sirf aap ke authorised records aur purchased services yahan show hoti hain.
                </p>
            </div>

            <div class="kes-agent-dashboard-actions">
                <article class="kes-agent-action-card">
                    <div class="kes-agent-action-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 3h16v18H4V3Zm3 4v2h10V7H7Zm0 4v2h10v-2H7Zm0 4v2h6v-2H7Z"/>
                        </svg>
                    </div>
                    <span>Finance</span>
                    <h3>Your Ledger / Balance</h3>
                    <p>Debit, credit aur closing balance ki secure account history dekhein.</p>
                    <a href="{{ route('customer.portal.ledger') }}">
                        View Ledger
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="m13 5 7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5Z"/>
                        </svg>
                    </a>
                </article>

                <article class="kes-agent-action-card">
                    <div class="kes-agent-action-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2a8 8 0 0 0-8 8c0 5.8 8 12 8 12s8-6.2 8-12a8 8 0 0 0-8-8Zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/>
                        </svg>
                    </div>
                    <span>Bookings</span>
                    <h3>Your Booking Tracking</h3>
                    <p>Visa, flight, Umrah aur hotel booking progress ek jagah track karein.</p>
                    <a href="{{ route('customer.portal.tracking') }}">
                        Open Tracking
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="m13 5 7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5Z"/>
                        </svg>
                    </a>
                </article>

                <article class="kes-agent-action-card">
                    <div class="kes-agent-action-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 2h9l5 5v15H6V2Zm8 2v5h4l-4-5ZM9 13v2h8v-2H9Zm0 4v2h6v-2H9Z"/>
                        </svg>
                    </div>
                    <span>Invoices</span>
                    <h3>Your Invoices</h3>
                    <p>Apne naam par generate hui invoices aur payable amounts check karein.</p>
                    <a href="{{ route('customer.portal.invoices') }}">
                        View Invoices
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="m13 5 7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5Z"/>
                        </svg>
                    </a>
                </article>

                <article class="kes-agent-action-card">
                    <div class="kes-agent-action-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Zm3 5v2h8V8H8Zm0 4v2h8v-2H8Z"/>
                        </svg>
                    </div>
                    <span>Vouchers</span>
                    <h3>Your Vouchers</h3>
                    <p>Umrah aur hotel vouchers ko secure portal se view aur download karein.</p>
                    <a href="{{ route('customer.portal.vouchers') }}">
                        View Vouchers
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="m13 5 7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5Z"/>
                        </svg>
                    </a>
                </article>

                <article class="kes-agent-action-card">
                    <div class="kes-agent-action-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3 7 8h3v5h4V8h3l-5-5ZM5 15v5h14v-5h2v7H3v-7h2Z"/>
                        </svg>
                    </div>
                    <span>Payments</span>
                    <h3>Upload Payment Proof</h3>
                    <p>Bank transfer, deposit, JazzCash ya EasyPaisa receipt safely upload karein.</p>
                    <a href="{{ route('customer.portal.payment_uploads') }}">
                        Upload Payment
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="m13 5 7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5Z"/>
                        </svg>
                    </a>
                </article>

                <article class="kes-agent-action-card">
                    <div class="kes-agent-action-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2a10 10 0 0 0-8.7 15l-1.1 4.1 4.2-1.1A10 10 0 1 0 12 2Zm-1 15H9v-2h2v2Zm3.1-6.1-.9.7c-.9.7-1.2 1.2-1.2 2.4h-2c0-1.9.6-2.9 1.9-3.9l.7-.5c.6-.5.9-.9.9-1.5 0-.9-.7-1.5-1.7-1.5-1.1 0-1.8.6-2 1.8l-2-.3c.3-2.2 1.8-3.5 4-3.5 2.3 0 3.8 1.4 3.8 3.4 0 1.2-.5 2.1-1.5 2.9Z"/>
                        </svg>
                    </div>
                    <span>Support</span>
                    <h3>Support Tickets</h3>
                    <p>Booking ya payment issue ke liye support request create aur track karein.</p>
                    <a href="{{ route('customer.portal.support') }}">
                        Open Support
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="m13 5 7 7-7 7-1.4-1.4 4.6-4.6H4v-2h12.2l-4.6-4.6L13 5Z"/>
                        </svg>
                    </a>
                </article>
            </div>
        </section>

        <section class="kes-agent-dashboard-offers" aria-label="Latest offers and promotions">
            <x-customer-latest-offers />
        </section>
    </div>
@endsection
