<x-filament-panels::page>
    @php
        $report = $this->reportData();
        $stats = $report['stats'];
        $supplierOptions = $this->supplierOptions();
        $serviceTypeOptions = $this->serviceTypeOptions();
    @endphp

    <style>
        .supplier-statement {
            display: grid;
            gap: 1.25rem;
        }

        .supplier-panel,
        .supplier-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .dark .supplier-panel,
        .dark .supplier-card {
            background: #111827;
            border-color: #374151;
        }

        .supplier-panel {
            padding: 1.25rem;
        }

        .supplier-title {
            margin: 0;
            color: #111827;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .dark .supplier-title {
            color: #f9fafb;
        }

        .supplier-description {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .82rem;
            line-height: 1.55;
        }

        .dark .supplier-description {
            color: #9ca3af;
        }

        .supplier-notice {
            border-left: 4px solid #0f766e;
        }

        .supplier-warning {
            border-left: 4px solid #b91c1c;
        }

        .supplier-filters {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 1rem;
            margin-top: 1rem;
            align-items: end;
        }

        .supplier-field label {
            display: block;
            margin-bottom: .4rem;
            color: #374151;
            font-size: .75rem;
            font-weight: 700;
        }

        .dark .supplier-field label {
            color: #d1d5db;
        }

        .supplier-field input,
        .supplier-field select {
            width: 100%;
            min-height: 2.65rem;
            padding: .55rem .75rem;
            border: 1px solid #d1d5db;
            border-radius: .65rem;
            background: white;
            color: #111827;
        }

        .dark .supplier-field input,
        .dark .supplier-field select {
            background: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .supplier-buttons {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .supplier-button {
            min-height: 2.65rem;
            border: 0;
            border-radius: .65rem;
            padding: .55rem .9rem;
            cursor: pointer;
            font-size: .78rem;
            font-weight: 700;
        }

        .supplier-button-primary {
            background: #0f766e;
            color: white;
        }

        .supplier-button-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .dark .supplier-button-secondary {
            background: #374151;
            color: #f9fafb;
        }

        .supplier-info {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: .8rem;
            margin-top: 1rem;
        }

        .supplier-info-item {
            padding: .8rem;
            border-radius: .7rem;
            background: #f9fafb;
        }

        .dark .supplier-info-item {
            background: #1f2937;
        }

        .supplier-info-label {
            color: #6b7280;
            font-size: .66rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .supplier-info-value {
            margin-top: .25rem;
            color: #111827;
            font-size: .8rem;
            font-weight: 700;
        }

        .dark .supplier-info-value {
            color: #f9fafb;
        }

        .supplier-cards {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .supplier-card {
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        .supplier-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #0f766e;
        }

        .supplier-card-danger::before {
            background: #b91c1c;
        }

        .supplier-card-warning::before {
            background: #d97706;
        }

        .supplier-card-label {
            color: #6b7280;
            font-size: .67rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .supplier-card-value {
            margin-top: .4rem;
            color: #111827;
            font-size: 1.25rem;
            font-weight: 900;
        }

        .dark .supplier-card-value {
            color: #f9fafb;
        }

        .supplier-table-wrap {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .supplier-table {
            width: 100%;
            min-width: 1350px;
            border-collapse: collapse;
        }

        .supplier-table th {
            padding: .72rem;
            text-align: left;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: .68rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .supplier-table th {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .supplier-table td {
            padding: .8rem .72rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: .77rem;
            vertical-align: middle;
        }

        .dark .supplier-table td {
            border-color: #374151;
            color: #e5e7eb;
        }

        .supplier-nowrap {
            white-space: nowrap;
        }

        .supplier-description-cell {
            min-width: 250px;
            max-width: 380px;
            white-space: normal;
        }

        .supplier-money {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
        }

        .supplier-badge {
            display: inline-flex;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .67rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .supplier-bill {
            background: #dbeafe;
            color: #1e40af;
        }

        .supplier-payment {
            background: #dcfce7;
            color: #166534;
        }

        .supplier-payable {
            background: #fee2e2;
            color: #991b1b;
        }

        .supplier-advance {
            background: #dcfce7;
            color: #166534;
        }

        .supplier-settled {
            background: #e5e7eb;
            color: #374151;
        }

        .supplier-open {
            display: inline-flex;
            padding: .4rem .65rem;
            border-radius: .5rem;
            background: #0f766e;
            color: white;
            text-decoration: none;
            font-size: .7rem;
            font-weight: 800;
        }

        .supplier-empty {
            padding: 2.5rem !important;
            text-align: center;
            color: #6b7280 !important;
        }

        .supplier-opening td {
            background: #f9fafb;
            font-weight: 800;
        }

        .dark .supplier-opening td {
            background: #1f2937;
        }

        @media (max-width: 1100px) {
            .supplier-filters,
            .supplier-cards,
            .supplier-info {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .supplier-filters,
            .supplier-cards,
            .supplier-info {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .supplier-no-print {
                display: none !important;
            }

            .supplier-panel,
            .supplier-card {
                box-shadow: none;
            }

            .supplier-open {
                display: none;
            }
        }
    </style>

    <div class="supplier-statement">
        <section class="supplier-panel supplier-notice">
            <h2 class="supplier-title">
                Unified Supplier Commercial Ledger
            </h2>

            <p class="supplier-description">
                Supplier Bills liability increase karte hain,
                jabke Approved Supplier Payments liability
                decrease karte hain. Positive closing balance
                supplier payable ko represent karta hai.
            </p>
        </section>

        <section class="supplier-panel supplier-no-print">
            <h2 class="supplier-title">
                Statement Filters
            </h2>

            <div class="supplier-filters">
                <div class="supplier-field">
                    <label for="supplierId">
                        Supplier
                    </label>

                    <select
                        id="supplierId"
                        wire:model.live="supplierId"
                    >
                        <option value="">
                            Select Supplier
                        </option>

                        @foreach (
                            $supplierOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="supplier-field">
                    <label for="dateFrom">
                        From Date
                    </label>

                    <input
                        id="dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                    >
                </div>

                <div class="supplier-field">
                    <label for="dateTo">
                        To Date
                    </label>

                    <input
                        id="dateTo"
                        type="date"
                        wire:model.live="dateTo"
                    >
                </div>

                <div class="supplier-field">
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

                <div class="supplier-buttons">
                    <button
                        type="button"
                        class="supplier-button supplier-button-secondary"
                        wire:click="resetFilters"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="supplier-button supplier-button-primary"
                        wire:click="$refresh"
                    >
                        Refresh
                    </button>

                    <button
                        type="button"
                        class="supplier-button supplier-button-primary"
                        onclick="window.print()"
                    >
                        Print
                    </button>
                </div>
            </div>
        </section>

        @if (! $report['database_ready'])
            <section class="supplier-panel supplier-warning">
                <h2 class="supplier-title">
                    Database Requirements Missing
                </h2>

                @foreach (
                    $report['missing_requirements']
                    as $requirement
                )
                    <p class="supplier-description">
                        {{ $requirement }}
                    </p>
                @endforeach
            </section>
        @elseif ($report['supplier_required'])
            <section class="supplier-panel">
                <h2 class="supplier-title">
                    Select Supplier
                </h2>

                <p class="supplier-description">
                    Statement generate karne ke liye
                    supplier select karein.
                </p>
            </section>
        @else
            <section class="supplier-panel">
                <h2 class="supplier-title">
                    {{ $report['supplier']['name'] }}
                </h2>

                <p class="supplier-description">
                    Statement Period:
                    {{ $report['period']['from'] }}
                    to
                    {{ $report['period']['to'] }}
                </p>

                <div class="supplier-info">
                    <div class="supplier-info-item">
                        <div class="supplier-info-label">
                            Supplier Type
                        </div>

                        <div class="supplier-info-value">
                            {{ $report['supplier']['supplier_type'] }}
                        </div>
                    </div>

                    <div class="supplier-info-item">
                        <div class="supplier-info-label">
                            Phone
                        </div>

                        <div class="supplier-info-value">
                            {{ $report['supplier']['phone'] }}
                        </div>
                    </div>

                    <div class="supplier-info-item">
                        <div class="supplier-info-label">
                            Email
                        </div>

                        <div class="supplier-info-value">
                            {{ $report['supplier']['email'] }}
                        </div>
                    </div>

                    <div class="supplier-info-item">
                        <div class="supplier-info-label">
                            Generated
                        </div>

                        <div class="supplier-info-value">
                            {{ $report['generated_at'] }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="supplier-cards">
                <article class="supplier-card">
                    <div class="supplier-card-label">
                        Opening Balance
                    </div>

                    <div class="supplier-card-value">
                        PKR
                        {{ number_format(
                            $stats['opening_balance'],
                            2
                        ) }}
                    </div>
                </article>

                <article class="supplier-card supplier-card-danger">
                    <div class="supplier-card-label">
                        Supplier Bills
                    </div>

                    <div class="supplier-card-value">
                        PKR
                        {{ number_format(
                            $stats['period_credit'],
                            2
                        ) }}
                    </div>
                </article>

                <article class="supplier-card">
                    <div class="supplier-card-label">
                        Supplier Payments
                    </div>

                    <div class="supplier-card-value">
                        PKR
                        {{ number_format(
                            $stats['period_debit'],
                            2
                        ) }}
                    </div>
                </article>

                <article
                    class="supplier-card {{
                        $report['closing_status_key']
                            === 'payable'
                                ? 'supplier-card-danger'
                                : ''
                    }}"
                >
                    <div class="supplier-card-label">
                        Closing Balance
                    </div>

                    <div class="supplier-card-value">
                        PKR
                        {{ number_format(
                            $stats['closing_balance'],
                            2
                        ) }}
                    </div>

                    <div style="margin-top: .4rem;">
                        <span
                            class="supplier-badge supplier-{{ $report['closing_status_key'] }}"
                        >
                            {{ $report['closing_status'] }}
                        </span>
                    </div>
                </article>

                <article class="supplier-card supplier-card-danger">
                    <div class="supplier-card-label">
                        Amount Payable
                    </div>

                    <div class="supplier-card-value">
                        PKR
                        {{ number_format(
                            $stats['amount_payable'],
                            2
                        ) }}
                    </div>
                </article>

                <article class="supplier-card">
                    <div class="supplier-card-label">
                        Supplier Advance
                    </div>

                    <div class="supplier-card-value">
                        PKR
                        {{ number_format(
                            $stats['supplier_advance'],
                            2
                        ) }}
                    </div>
                </article>

                <article class="supplier-card">
                    <div class="supplier-card-label">
                        Transactions
                    </div>

                    <div class="supplier-card-value">
                        {{ number_format(
                            $stats['transaction_count']
                        ) }}
                    </div>
                </article>

                <article class="supplier-card">
                    <div class="supplier-card-label">
                        Bills / Payments
                    </div>

                    <div class="supplier-card-value">
                        {{ $stats['bill_count'] }}
                        /
                        {{ $stats['payment_count'] }}
                    </div>
                </article>
            </section>

            <section class="supplier-panel">
                <h2 class="supplier-title">
                    Supplier Ledger Transactions
                </h2>

                <div class="supplier-table-wrap">
                    <table class="supplier-table">
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
                            <tr class="supplier-opening">
                                <td colspan="8">
                                    Opening Balance before
                                    {{ $report['period']['from'] }}
                                </td>

                                <td class="supplier-money">—</td>
                                <td class="supplier-money">—</td>

                                <td class="supplier-money">
                                    PKR
                                    {{ number_format(
                                        $stats['opening_balance'],
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
                                <tr>
                                    <td class="supplier-nowrap">
                                        {{ $row['date'] }}
                                    </td>

                                    <td class="supplier-nowrap">
                                        <span
                                            class="supplier-badge supplier-{{ $row['transaction_key'] }}"
                                        >
                                            {{ $row['transaction_type'] }}
                                        </span>
                                    </td>

                                    <td class="supplier-nowrap">
                                        <strong>
                                            {{ $row['source_no'] }}
                                        </strong>
                                    </td>

                                    <td class="supplier-nowrap">
                                        {{ $row['service_type'] }}
                                    </td>

                                    <td class="supplier-nowrap">
                                        {{ $row['reference_no'] }}
                                    </td>

                                    <td class="supplier-description-cell">
                                        {{ $row['description'] }}
                                    </td>

                                    <td class="supplier-nowrap">
                                        {{ $row['payment_method'] }}
                                    </td>

                                    <td class="supplier-nowrap">
                                        {{ $row['status'] }}
                                    </td>

                                    <td class="supplier-money">
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

                                    <td class="supplier-money">
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

                                    <td class="supplier-money">
                                        PKR
                                        {{ number_format(
                                            $row['running_balance'],
                                            2
                                        ) }}
                                    </td>

                                    <td class="supplier-nowrap">
                                        <span
                                            class="supplier-badge supplier-{{ $row['balance_key'] }}"
                                        >
                                            {{ $row['balance_label'] }}
                                        </span>
                                    </td>

                                    <td class="supplier-nowrap">
                                        <a
                                            href="{{ $row['source_url'] }}"
                                            class="supplier-open"
                                        >
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="13"
                                        class="supplier-empty"
                                    >
                                        Selected period ke liye
                                        koi supplier transaction
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