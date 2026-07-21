@extends('customer-portal.layout')

@section('content')
    <style>
        .cp-insurance-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .cp-insurance-card {
            margin-bottom: 18px;
            padding: 20px;
            background: #ffffff;
            border: 1px solid #dbe6df;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 65, 42, 0.06);
        }

        .cp-insurance-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 16px;
        }

        .cp-insurance-heading h2 {
            margin: 0 0 5px;
            font-size: 1.15rem;
        }

        .cp-insurance-heading p {
            margin: 0;
            color: #64748b;
        }

        .cp-insurance-badges,
        .cp-insurance-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .cp-insurance-badge {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            background: #edf7f1;
            color: #14532d;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .cp-insurance-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .cp-insurance-field {
            padding: 12px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .cp-insurance-field span {
            display: block;
            margin-bottom: 5px;
            color: #64748b;
            font-size: 0.78rem;
        }

        .cp-insurance-field strong {
            display: block;
            overflow-wrap: anywhere;
        }

        .cp-insurance-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 9px 14px;
            color: #ffffff;
            background: #17643d;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
        }

        .cp-insurance-action.secondary {
            color: #17643d;
            background: #edf7f1;
            border: 1px solid #bcd8c8;
        }

        .cp-insurance-payments {
            margin-top: 20px;
        }

        .cp-insurance-payments h3 {
            margin: 0 0 10px;
            font-size: 1rem;
        }

        .cp-insurance-empty {
            padding: 32px;
            text-align: center;
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
        }

        @media (max-width: 980px) {
            .cp-insurance-summary,
            .cp-insurance-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .cp-insurance-summary,
            .cp-insurance-grid {
                grid-template-columns: 1fr;
            }

            .cp-insurance-heading {
                flex-direction: column;
            }
        }
    </style>

    <section class="cp-page-head">
        <div>
            <h1>Travel Insurance</h1>
            <p>
                View your approved policies, payment history,
                invoices and private documents.
            </p>
        </div>
    </section>

    <section
        class="cp-insurance-summary"
        aria-label="Travel Insurance summary"
    >
        <article class="cp-stat-card">
            <span>Total Policies</span>
            <strong>{{ number_format($summary['total']) }}</strong>
        </article>

        <article class="cp-stat-card">
            <span>Active Policies</span>
            <strong>{{ number_format($summary['active']) }}</strong>
        </article>

        <article class="cp-stat-card">
            <span>Expiring in 30 Days</span>
            <strong>{{ number_format($summary['expiring_soon']) }}</strong>
        </article>

        <article class="cp-stat-card">
            <span>Outstanding Balance</span>
            <strong>
                PKR {{ number_format($summary['outstanding'], 2) }}
            </strong>
        </article>
    </section>

    @forelse ($policies as $policy)
        <article class="cp-insurance-card">
            <header class="cp-insurance-heading">
                <div>
                    <h2>{{ $policy->policy_no }}</h2>

                    <p>
                        {{ $policy->traveller_name ?: 'Traveller details unavailable' }}
                    </p>
                </div>

                <div class="cp-insurance-badges">
                    <span class="cp-insurance-badge">
                        {{ $policy->status ?: 'Pending' }}
                    </span>

                    <span class="cp-insurance-badge">
                        {{ $policy->payment_status ?: 'Unpaid' }}
                    </span>
                </div>
            </header>

            <div class="cp-insurance-grid">
                <div class="cp-insurance-field">
                    <span>Provider</span>
                    <strong>{{ $policy->provider?->name ?: '-' }}</strong>
                </div>

                <div class="cp-insurance-field">
                    <span>Destination</span>
                    <strong>{{ $policy->destination_country ?: '-' }}</strong>
                </div>

                <div class="cp-insurance-field">
                    <span>Travel Period</span>
                    <strong>
                        {{ $policy->travel_start_date?->format('d M Y') ?: '-' }}
                        —
                        {{ $policy->travel_end_date?->format('d M Y') ?: '-' }}
                    </strong>
                </div>

                <div class="cp-insurance-field">
                    <span>Policy Validity</span>
                    <strong>
                        {{ $policy->policy_start_date?->format('d M Y') ?: '-' }}
                        —
                        {{ $policy->policy_end_date?->format('d M Y') ?: '-' }}
                    </strong>
                </div>

                <div class="cp-insurance-field">
                    <span>Coverage</span>
                    <strong>
                        {{ $policy->coverage_currency ?: $policy->currency }}
                        {{ number_format((float) $policy->coverage_amount, 2) }}
                    </strong>
                </div>

                <div class="cp-insurance-field">
                    <span>Total Amount</span>
                    <strong>
                        {{ $policy->currency ?: 'PKR' }}
                        {{ number_format((float) $policy->total_amount, 2) }}
                    </strong>
                </div>

                <div class="cp-insurance-field">
                    <span>Paid Amount</span>
                    <strong>
                        {{ $policy->currency ?: 'PKR' }}
                        {{ number_format((float) $policy->paid_amount, 2) }}
                    </strong>
                </div>

                <div class="cp-insurance-field">
                    <span>Balance</span>
                    <strong>
                        {{ $policy->currency ?: 'PKR' }}
                        {{ number_format((float) $policy->balance_amount, 2) }}
                    </strong>
                </div>
            </div>

            <div class="cp-insurance-actions">
                @if (filled($policy->policy_document_path))
                    <a
                        class="cp-insurance-action"
                        href="{{ route(
                            'customer.portal.travel_insurance.policy_document',
                            $policy
                        ) }}"
                        aria-label="Download policy document for {{ $policy->policy_no }}"
                    >
                        Download Policy
                    </a>
                @endif

                @if ($policy->invoice)
                    <a
                        class="cp-insurance-action secondary"
                        href="{{ route('customer.portal.invoices') }}"
                        aria-label="Open linked invoice {{ $policy->invoice->invoice_no }}"
                    >
                        Invoice {{ $policy->invoice->invoice_no }}
                        · {{ $policy->invoice->status }}
                    </a>
                @endif
            </div>

            <section
                class="cp-insurance-payments"
                aria-label="Approved payments for {{ $policy->policy_no }}"
            >
                <h3>Approved Payments</h3>

                @if ($policy->payments->isEmpty())
                    <p>No approved payments are available for this policy.</p>
                @else
                    <div class="cp-table-wrap">
                        <table class="cp-table">
                            <caption class="sr-only">
                                Approved payments for policy {{ $policy->policy_no }}
                            </caption>

                            <thead>
                                <tr>
                                    <th>Payment No</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th class="right">Amount</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($policy->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_no ?: '-' }}</td>

                                        <td>
                                            {{ $payment->payment_date?->format('d M Y') ?: '-' }}
                                        </td>

                                        <td>{{ $payment->payment_method ?: '-' }}</td>

                                        <td>{{ $payment->reference_no ?: '-' }}</td>

                                        <td class="right">
                                            {{ $policy->currency ?: 'PKR' }}
                                            {{ number_format((float) $payment->amount, 2) }}
                                        </td>

                                        <td>
                                            @if (filled($payment->receipt_path))
                                                <a
                                                    href="{{ route(
                                                        'customer.portal.travel_insurance.payment_receipt',
                                                        $payment
                                                    ) }}"
                                                    aria-label="Download receipt for payment {{ $payment->payment_no }}"
                                                >
                                                    Download
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </article>
    @empty
        <div class="cp-insurance-empty">
            <h2>No approved Travel Insurance policies found</h2>

            <p>
                Your approved policies will appear here after office processing.
            </p>
        </div>
    @endforelse

    @include(
        'customer-portal.partials.pagination',
        [
            'paginator' => $policies,
        ]
    )
@endsection