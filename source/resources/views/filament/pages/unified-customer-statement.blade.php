<x-filament-panels::page>
    @php
        $report = $this->reportData();
        $stats = $report['stats'];

        $customerOptions =
            $this->customerOptions();

        $serviceTypeOptions =
            $this->serviceTypeOptions();
    @endphp

    <style>
        .customer-statement {
            display: grid;
            gap: 1.25rem;
        }

        .statement-panel,
        .statement-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .dark .statement-panel,
        .dark .statement-card {
            background: #111827;
            border-color: #374151;
        }

        .statement-panel {
            padding: 1.25rem;
        }

        .statement-title {
            margin: 0;
            color: #111827;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .dark .statement-title {
            color: #f9fafb;
        }

        .statement-description {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .83rem;
            line-height: 1.55;
        }

        .dark .statement-description {
            color: #9ca3af;
        }

        .statement-notice {
            border-left: 4px solid #0f766e;
        }

        .statement-warning {
            border-left: 4px solid #b91c1c;
        }

        .statement-filters {
            display: grid;
            grid-template-columns:
                2fr 1fr 1fr 1fr auto;
            gap: 1rem;
            margin-top: 1rem;
            align-items: end;
        }

        .statement-field label {
            display: block;
            margin-bottom: .4rem;
            color: #374151;
            font-size: .76rem;
            font-weight: 700;
        }

        .dark .statement-field label {
            color: #d1d5db;
        }

        .statement-field input,
        .statement-field select {
            width: 100%;
            min-height: 2.65rem;
            padding: .55rem .75rem;
            border: 1px solid #d1d5db;
            border-radius: .65rem;
            background: white;
            color: #111827;
        }

        .dark .statement-field input,
        .dark .statement-field select {
            background: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .statement-buttons {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .statement-button {
            min-height: 2.65rem;
            border: 0;
            border-radius: .65rem;
            padding: .55rem .9rem;
            cursor: pointer;
            font-size: .79rem;
            font-weight: 700;
        }

        .statement-button-primary {
            background: #0f766e;
            color: white;
        }

        .statement-button-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .dark .statement-button-secondary {
            background: #374151;
            color: #f9fafb;
        }

        .customer-header {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
        }

        .customer-meta {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: .8rem;
            margin-top: 1rem;
        }

        .customer-meta-item {
            padding: .75rem;
            background: #f9fafb;
            border-radius: .7rem;
        }

        .dark .customer-meta-item {
            background: #1f2937;
        }

        .customer-meta-label {
            color: #6b7280;
            font-size: .67rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .customer-meta-value {
            margin-top: .25rem;
            color: #111827;
            font-size: .8rem;
            font-weight: 700;
        }

        .dark .customer-meta-value {
            color: #f9fafb;
        }

        .statement-cards {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .statement-card {
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        .statement-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #0f766e;
        }

        .statement-card-danger::before {
            background: #b91c1c;
        }

        .statement-card-warning::before {
            background: #d97706;
        }

        .statement-card-label {
            color: #6b7280;
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .dark .statement-card-label {
            color: #9ca3af;
        }

        .statement-card-value {
            margin-top: .4rem;
            color: #111827;
            font-size: 1.25rem;
            font-weight: 900;
        }

        .dark .statement-card-value {
            color: #f9fafb;
        }

        .statement-table-wrap {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .statement-table {
            width: 100%;
            min-width: 1380px;
            border-collapse: collapse;
        }

        .statement-table th {
            padding: .72rem;
            text-align: left;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: .68rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .statement-table th {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .statement-table td {
            padding: .8rem .72rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: .77rem;
            vertical-align: middle;
        }

        .dark .statement-table td {
            border-color: #374151;
            color: #e5e7eb;
        }

        .statement-nowrap {
            white-space: nowrap;
        }

        .statement-description-cell {
            min-width: 260px;
            max-width: 380px;
            white-space: normal;
            line-height: 1.45;
        }

        .statement-money {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
        }

        .statement-badge {
            display: inline-flex;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .67rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .statement-invoice {
            background: #dbeafe;
            color: #1e40af;
        }

        .statement-receipt {
            background: #dcfce7;
            color: #166534;
        }

        .statement-refund {
            background: #fee2e2;
            color: #991b1b;
        }

        .statement-receivable {
            background: #fee2e2;
            color: #991b1b;
        }

        .statement-advance {
            background: #dcfce7;
            color: #166534;
        }

        .statement-settled {
            background: #e5e7eb;
            color: #374151;
        }

        .statement-open {
            display: inline-flex;
            padding: .4rem .65rem;
            border-radius: .5rem;
            background: #0f766e;
            color: white;
            text-decoration: none;
            font-size: .7rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .statement-empty {
            padding: 2.5rem !important;
            text-align: center;
            color: #6b7280 !important;
        }

        .opening-row td {
            background: #f9fafb;
            font-weight: 800;
        }

        .dark .opening-row td {
            background: #1f2937;
        }

        @media (max-width: 1150px) {
            .statement-filters {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .statement-cards {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .statement-filters,
            .statement-cards,
            .customer-header,
            .customer-meta {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .statement-no-print {
                display: none !important;
            }

            .statement-panel,
            .statement-card {
                box-shadow: none;
                break-inside: avoid;
            }

            .statement-table {
                min-width: 100%;
                font-size: 8px;
            }

            .statement-open {
                display: none;
            }
        }
    </style>

    <div class="customer-statement">
        <section class="statement-panel statement-notice">
            <h2 class="statement-title">
                Unified Customer Commercial Ledger
            </h2>

            <p class="statement-description">
                Ye statement central Invoice, Customer
                Receipt aur Refund workflow use karti hai.
                Legacy service payment tables ko intentionally
                include nahi kiya gaya, taake same payment
                double count na ho.
            </p>
        </section>

        <section class="statement-panel statement-no-print">
            <h2 class="statement-title">
                Statement Filters
            </h2>

            <div class="statement-filters">
                <div class="statement-field">
                    <label for="customerId">
                        Customer / Agent
                    </label>

                    <select
                        id="customerId"
                        wire:model.live="customerId"
                    >
                        <option value="">
                            Select Customer / Agent
                        </option>

                        @foreach (
                            $customerOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="statement-field">
                    <label for="dateFrom">
                        From Date
                    </label>

                    <input
                        id="dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                    >
                </div>

                <div class="statement-field">
                    <label for="dateTo">
                        To Date
                    </label>

                    <input
                        id="dateTo"
                        type="date"
                        wire:model.live="dateTo"
                    >
                </div>

                <div class="statement-field">
                    <label for="serviceType">
                        Service
                    </label>

                    <select
                        id="serviceType"
                        wire:model.live="serviceType"
                    >
                        @foreach (
                            $serviceTypeOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="statement-buttons">
                    <button
                        type="button"
                        class="statement-button statement-button-secondary"
                        wire:click="resetFilters"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="statement-button statement-button-primary"
                        wire:click="$refresh"
                    >
                        Refresh
                    </button>

                    <button
                        type="button"
                        class="statement-button statement-button-primary"
                        onclick="window.print()"
                    >
                        Print
                    </button>
                </div>
            </div>
        </section>

        @if (! $report['database_ready'])
            <section
                class="statement-panel statement-warning"
            >
                <h2 class="statement-title">
                    Database Migration Required
                </h2>

                @foreach (
                    $report['missing_requirements']
                    as $requirement
                )
                    <p class="statement-description">
                        {{ $requirement }}
                    </p>
                @endforeach
            </section>
        @elseif ($report['customer_required'])
            <section class="statement-panel">
                <h2 class="statement-title">
                    Select Customer / Agent
                </h2>

                <p class="statement-description">
                    Statement generate karne ke liye
                    Customer / Agent select karein.
                </p>
            </section>
        @else
            <section class="statement-panel">
                <div class="customer-header">
                    <div>
                        <h2 class="statement-title">
                            {{ $report['customer']['name'] }}
                        </h2>

                        <p class="statement-description">
                            {{ $report['customer']['customer_type'] }}
                            —
                            {{ $report['customer']['status'] }}
                        </p>
                    </div>

                    <div>
                        <h2 class="statement-title">
                            Statement Period
                        </h2>

                        <p class="statement-description">
                            {{ $report['period']['from'] }}
                            to
                            {{ $report['period']['to'] }}
                        </p>
                    </div>
                </div>

                <div class="customer-meta">
                    <div class="customer-meta-item">
                        <div class="customer-meta-label">
                            Mobile
                        </div>

                        <div class="customer-meta-value">
                            {{ $report['customer']['mobile'] }}
                        </div>
                    </div>

                    <div class="customer-meta-item">
                        <div class="customer-meta-label">
                            WhatsApp
                        </div>

                        <div class="customer-meta-value">
                            {{ $report['customer']['whatsapp'] }}
                        </div>
                    </div>

                    <div class="customer-meta-item">
                        <div class="customer-meta-label">
                            Email
                        </div>

                        <div class="customer-meta-value">
                            {{ $report['customer']['email'] }}
                        </div>
                    </div>

                    <div class="customer-meta-item">
                        <div class="customer-meta-label">
                            CNIC
                        </div>

                        <div class="customer-meta-value">
                            {{ $report['customer']['cnic'] }}
                        </div>
                    </div>

                    <div class="customer-meta-item">
                        <div class="customer-meta-label">
                            Passport
                        </div>

                        <div class="customer-meta-value">
                            {{ $report['customer']['passport_no'] }}
                        </div>
                    </div>

                    <div class="customer-meta-item">
                        <div class="customer-meta-label">
                            Generated
                        </div>

                        <div class="customer-meta-value">
                            {{ $report['generated_at'] }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="statement-cards">
                <article class="statement-card">
                    <div class="statement-card-label">
                        Opening Balance
                    </div>

                    <div class="statement-card-value">
                        PKR
                        {{ number_format(
                            $stats['opening_balance'],
                            2
                        ) }}
                    </div>
                </article>

                <article
                    class="statement-card statement-card-danger"
                >
                    <div class="statement-card-label">
                        Period Debit
                    </div>

                    <div class="statement-card-value">
                        PKR
                        {{ number_format(
                            $stats['period_debit'],
                            2
                        ) }}
                    </div>
                </article>

                <article class="statement-card">
                    <div class="statement-card-label">
                        Period Credit
                    </div>

                    <div class="statement-card-value">
                        PKR
                        {{ number_format(
                            $stats['period_credit'],
                            2
                        ) }}
                    </div>
                </article>

                <article
                    class="statement-card {{
                        $report['closing_status_key']
                            === 'receivable'
                                ? 'statement-card-danger'
                                : ''
                    }}"
                >
                    <div class="statement-card-label">
                        Closing Balance
                    </div>

                    <div class="statement-card-value">
                        PKR
                        {{ number_format(
                            $stats['closing_balance'],
                            2
                        ) }}
                    </div>

                    <div style="margin-top: .4rem;">
                        <span
                            class="statement-badge statement-{{ $report['closing_status_key'] }}"
                        >
                            {{ $report['closing_status'] }}
                        </span>
                    </div>
                </article>

                <article
                    class="statement-card statement-card-danger"
                >
                    <div class="statement-card-label">
                        Amount Receivable
                    </div>

                    <div class="statement-card-value">
                        PKR
                        {{ number_format(
                            $stats['amount_receivable'],
                            2
                        ) }}
                    </div>
                </article>

                <article class="statement-card">
                    <div class="statement-card-label">
                        Advance Available
                    </div>

                    <div class="statement-card-value">
                        PKR
                        {{ number_format(
                            $stats['advance_available'],
                            2
                        ) }}
                    </div>
                </article>

                <article class="statement-card">
                    <div class="statement-card-label">
                        Transactions
                    </div>

                    <div class="statement-card-value">
                        {{ number_format(
                            $stats['transaction_count']
                        ) }}
                    </div>
                </article>

                <article class="statement-card">
                    <div class="statement-card-label">
                        Invoices / Receipts / Refunds
                    </div>

                    <div class="statement-card-value">
                        {{ $stats['invoice_count'] }}
                        /
                        {{ $stats['receipt_count'] }}
                        /
                        {{ $stats['refund_count'] }}
                    </div>
                </article>
            </section>

            <section class="statement-panel">
                <h2 class="statement-title">
                    Customer Ledger Transactions
                </h2>

                <div class="statement-table-wrap">
                    <table class="statement-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction</th>
                                <th>Reference</th>
                                <th>Service</th>
                                <th>External Ref</th>
                                <th>Description</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Running Balance</th>
                                <th>Balance Nature</th>
                                <th>Review</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="opening-row">
                                <td colspan="8">
                                    Opening Balance
                                    before
                                    {{ $report['period']['from'] }}
                                </td>

                                <td class="statement-money">
                                    —
                                </td>

                                <td class="statement-money">
                                    —
                                </td>

                                <td class="statement-money">
                                    PKR
                                    {{ number_format(
                                        $stats[
                                            'opening_balance'
                                        ],
                                        2
                                    ) }}
                                </td>

                                <td colspan="2">
                                    Opening
                                </td>
                            </tr>

                            @forelse (
                                $report['rows']
                                as $row
                            )
                                @php
                                    $typeClass =
                                        $row['transaction_key']
                                        === 'invoice'
                                            ? 'invoice'
                                            : (
                                                str_contains(
                                                    $row[
                                                        'transaction_key'
                                                    ],
                                                    'refund'
                                                )
                                                    ? 'refund'
                                                    : 'receipt'
                                            );
                                @endphp

                                <tr>
                                    <td class="statement-nowrap">
                                        {{ $row['date'] }}
                                    </td>

                                    <td class="statement-nowrap">
                                        <span
                                            class="statement-badge statement-{{ $typeClass }}"
                                        >
                                            {{ $row[
                                                'transaction_type'
                                            ] }}
                                        </span>
                                    </td>

                                    <td class="statement-nowrap">
                                        <strong>
                                            {{ $row['source_no'] }}
                                        </strong>
                                    </td>

                                    <td class="statement-nowrap">
                                        {{ $row['service_type'] }}
                                    </td>

                                    <td class="statement-nowrap">
                                        {{ $row['reference_no'] }}
                                    </td>

                                    <td
                                        class="statement-description-cell"
                                    >
                                        {{ $row['description'] }}
                                    </td>

                                    <td class="statement-nowrap">
                                        {{ $row['payment_method'] }}
                                    </td>

                                    <td class="statement-nowrap">
                                        {{ $row['status'] }}
                                    </td>

                                    <td class="statement-money">
                                        @if ($row['debit'] > 0)
                                            PKR
                                            {{ number_format(
                                                $row['debit'],
                                                2
                                            ) }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td class="statement-money">
                                        @if ($row['credit'] > 0)
                                            PKR
                                            {{ number_format(
                                                $row['credit'],
                                                2
                                            ) }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td class="statement-money">
                                        PKR
                                        {{ number_format(
                                            $row[
                                                'running_balance'
                                            ],
                                            2
                                        ) }}
                                    </td>

                                    <td class="statement-nowrap">
                                        <span
                                            class="statement-badge statement-{{ $row['balance_key'] }}"
                                        >
                                            {{ $row[
                                                'balance_label'
                                            ] }}
                                        </span>
                                    </td>

                                    <td class="statement-nowrap">
                                        <a
                                            href="{{ $row['source_url'] }}"
                                            class="statement-open"
                                        >
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="13"
                                        class="statement-empty"
                                    >
                                        Selected period ke liye
                                        koi statement transaction
                                        nahi mila.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-filament-panels::page>