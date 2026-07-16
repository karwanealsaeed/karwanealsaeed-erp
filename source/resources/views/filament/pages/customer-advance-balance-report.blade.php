<x-filament-panels::page>
    @php
        $report = $this->reportData();
        $stats = $report['stats'];

        $customerTypeOptions =
            $this->customerTypeOptions();

        $customerStatusOptions =
            $this->customerStatusOptions();

        $balanceFilterOptions =
            $this->balanceFilterOptions();
    @endphp

    <style>
        .advance-report {
            display: grid;
            gap: 1.25rem;
        }

        .advance-panel,
        .advance-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .dark .advance-panel,
        .dark .advance-card {
            background: #111827;
            border-color: #374151;
        }

        .advance-panel {
            padding: 1.25rem;
        }

        .advance-title {
            margin: 0;
            color: #111827;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .dark .advance-title {
            color: #f9fafb;
        }

        .advance-description {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .84rem;
            line-height: 1.55;
        }

        .dark .advance-description {
            color: #9ca3af;
        }

        .advance-notice {
            border-left: 4px solid #0f766e;
        }

        .advance-warning {
            border-left: 4px solid #b91c1c;
        }

        .advance-warning-list {
            margin: .75rem 0 0;
            padding-left: 1.25rem;
            color: #b91c1c;
            font-size: .82rem;
        }

        .advance-filters {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .advance-field label {
            display: block;
            margin-bottom: .4rem;
            color: #374151;
            font-size: .76rem;
            font-weight: 700;
        }

        .dark .advance-field label {
            color: #d1d5db;
        }

        .advance-field input,
        .advance-field select {
            width: 100%;
            min-height: 2.65rem;
            padding: .55rem .75rem;
            border: 1px solid #d1d5db;
            border-radius: .65rem;
            background: white;
            color: #111827;
        }

        .dark .advance-field input,
        .dark .advance-field select {
            background: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .advance-buttons {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            align-items: end;
        }

        .advance-button {
            min-height: 2.65rem;
            border: 0;
            border-radius: .65rem;
            padding: .55rem .9rem;
            cursor: pointer;
            font-size: .8rem;
            font-weight: 700;
        }

        .advance-button-primary {
            background: #0f766e;
            color: white;
        }

        .advance-button-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .dark .advance-button-secondary {
            background: #374151;
            color: #f9fafb;
        }

        .advance-cards {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .advance-card {
            padding: 1.05rem;
            position: relative;
            overflow: hidden;
        }

        .advance-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #0f766e;
        }

        .advance-card-warning::before {
            background: #d97706;
        }

        .advance-card-danger::before {
            background: #b91c1c;
        }

        .advance-card-label {
            color: #6b7280;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .dark .advance-card-label {
            color: #9ca3af;
        }

        .advance-card-value {
            margin-top: .4rem;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 900;
        }

        .dark .advance-card-value {
            color: #f9fafb;
        }

        .advance-table-wrap {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .advance-table {
            width: 100%;
            min-width: 1250px;
            border-collapse: collapse;
        }

        .advance-table th {
            padding: .72rem;
            text-align: left;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: .69rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .advance-table th {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .advance-table td {
            padding: .8rem .72rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: .78rem;
            vertical-align: middle;
        }

        .dark .advance-table td {
            border-color: #374151;
            color: #e5e7eb;
        }

        .advance-nowrap {
            white-space: nowrap;
        }

        .advance-money {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
        }

        .advance-badge {
            display: inline-flex;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .advance-positive {
            background: #dcfce7;
            color: #166534;
        }

        .advance-zero {
            background: #e5e7eb;
            color: #374151;
        }

        .advance-negative {
            background: #fee2e2;
            color: #991b1b;
        }

        .advance-type {
            background: #dbeafe;
            color: #1e40af;
        }

        .advance-open {
            display: inline-flex;
            padding: .45rem .7rem;
            border-radius: .55rem;
            background: #0f766e;
            color: white;
            text-decoration: none;
            font-size: .72rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .advance-empty {
            padding: 2.5rem !important;
            text-align: center;
            color: #6b7280 !important;
        }

        @media (max-width: 1100px) {
            .advance-cards {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .advance-filters {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .advance-cards,
            .advance-filters {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .advance-no-print {
                display: none !important;
            }

            .advance-panel,
            .advance-card {
                box-shadow: none;
                break-inside: avoid;
            }

            .advance-table {
                min-width: 100%;
                font-size: 8px;
            }

            .advance-open {
                display: none;
            }
        }
    </style>

    <div class="advance-report">
        <section
            class="advance-panel advance-notice"
        >
            <h2 class="advance-title">
                Customer Advance Liability
            </h2>

            <p class="advance-description">
                Approved Customer Advance receipts me se
                approved Customer Advance Refunds minus
                karke available balance calculate kiya
                gaya hai. Report selected As of Date tak
                ke records show karti hai.
            </p>
        </section>

        @if (! $report['database_ready'])
            <section
                class="advance-panel advance-warning"
            >
                <h2 class="advance-title">
                    Database Migration Required
                </h2>

                <p class="advance-description">
                    Report ke required tables ya columns
                    abhi available nahi hain.
                </p>

                <ul class="advance-warning-list">
                    @foreach (
                        $report['missing_requirements']
                        as $requirement
                    )
                        <li>{{ $requirement }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section
            class="advance-panel advance-no-print"
        >
            <h2 class="advance-title">
                Report Filters
            </h2>

            <div class="advance-filters">
                <div class="advance-field">
                    <label for="asOfDate">
                        As of Date
                    </label>

                    <input
                        id="asOfDate"
                        type="date"
                        wire:model.live="asOfDate"
                    >
                </div>

                <div class="advance-field">
                    <label for="customerType">
                        Customer Type
                    </label>

                    <select
                        id="customerType"
                        wire:model.live="customerType"
                    >
                        @foreach (
                            $customerTypeOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="advance-field">
                    <label for="customerStatus">
                        Customer Status
                    </label>

                    <select
                        id="customerStatus"
                        wire:model.live="customerStatus"
                    >
                        @foreach (
                            $customerStatusOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="advance-field">
                    <label for="balanceFilter">
                        Balance Filter
                    </label>

                    <select
                        id="balanceFilter"
                        wire:model.live="balanceFilter"
                    >
                        @foreach (
                            $balanceFilterOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="advance-field">
                    <label for="search">
                        Search Customer
                    </label>

                    <input
                        id="search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Name, mobile, CNIC..."
                    >
                </div>

                <div class="advance-buttons">
                    <button
                        type="button"
                        class="advance-button advance-button-secondary"
                        wire:click="resetFilters"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="advance-button advance-button-primary"
                        wire:click="$refresh"
                    >
                        Refresh
                    </button>

                    <button
                        type="button"
                        class="advance-button advance-button-primary"
                        onclick="window.print()"
                    >
                        Print
                    </button>
                </div>
            </div>
        </section>

        <section class="advance-cards">
            <article class="advance-card">
                <div class="advance-card-label">
                    Customers / Agents
                </div>

                <div class="advance-card-value">
                    {{ number_format(
                        $stats['customers']
                    ) }}
                </div>
            </article>

            <article class="advance-card">
                <div class="advance-card-label">
                    Total Advances Received
                </div>

                <div class="advance-card-value">
                    PKR
                    {{ number_format(
                        $stats['total_advances'],
                        2
                    ) }}
                </div>
            </article>

            <article
                class="advance-card advance-card-warning"
            >
                <div class="advance-card-label">
                    Total Advances Refunded
                </div>

                <div class="advance-card-value">
                    PKR
                    {{ number_format(
                        $stats['total_refunds'],
                        2
                    ) }}
                </div>
            </article>

            <article class="advance-card">
                <div class="advance-card-label">
                    Net Advance Liability
                </div>

                <div class="advance-card-value">
                    PKR
                    {{ number_format(
                        $stats[
                            'net_advance_balance'
                        ],
                        2
                    ) }}
                </div>
            </article>

            <article class="advance-card">
                <div class="advance-card-label">
                    Positive Balances
                </div>

                <div class="advance-card-value">
                    {{ number_format(
                        $stats[
                            'positive_balances'
                        ]
                    ) }}
                </div>
            </article>

            <article class="advance-card">
                <div class="advance-card-label">
                    Fully Refunded
                </div>

                <div class="advance-card-value">
                    {{ number_format(
                        $stats['fully_refunded']
                    ) }}
                </div>
            </article>

            <article
                class="advance-card advance-card-danger"
            >
                <div class="advance-card-label">
                    Over Refunded
                </div>

                <div class="advance-card-value">
                    {{ number_format(
                        $stats['over_refunded']
                    ) }}
                </div>
            </article>
        </section>

        <section class="advance-panel">
            <h2 class="advance-title">
                Customer Advance Balances
            </h2>

            <p class="advance-description">
                As of:
                {{ $report['as_of_date'] }}

                — Generated:
                {{ $report['generated_at'] }}
            </p>

            <div class="advance-table-wrap">
                <table class="advance-table">
                    <thead>
                        <tr>
                            <th>Customer / Agent</th>
                            <th>Type</th>
                            <th>Mobile</th>
                            <th>Advance Receipts</th>
                            <th>Advances Received</th>
                            <th>Advance Refunds</th>
                            <th>Refunded Amount</th>
                            <th>Available Balance</th>
                            <th>Balance Status</th>
                            <th>Last Advance</th>
                            <th>Last Refund</th>
                            <th>Customer Status</th>
                            <th>Review</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (
                            $report['rows']
                            as $row
                        )
                            <tr>
                                <td>
                                    <strong>
                                        {{ $row[
                                            'customer_name'
                                        ] }}
                                    </strong>

                                    <div
                                        style="
                                            margin-top: .2rem;
                                            color: #6b7280;
                                            font-size: .7rem;
                                        "
                                    >
                                        {{ $row['email'] }}
                                    </div>
                                </td>

                                <td class="advance-nowrap">
                                    <span
                                        class="advance-badge advance-type"
                                    >
                                        {{ $row[
                                            'customer_type'
                                        ] }}
                                    </span>
                                </td>

                                <td class="advance-nowrap">
                                    {{ $row['mobile'] }}
                                </td>

                                <td class="advance-money">
                                    {{ number_format(
                                        $row[
                                            'advance_count'
                                        ]
                                    ) }}
                                </td>

                                <td class="advance-money">
                                    PKR
                                    {{ number_format(
                                        $row[
                                            'total_advances'
                                        ],
                                        2
                                    ) }}
                                </td>

                                <td class="advance-money">
                                    {{ number_format(
                                        $row[
                                            'refund_count'
                                        ]
                                    ) }}
                                </td>

                                <td class="advance-money">
                                    PKR
                                    {{ number_format(
                                        $row[
                                            'total_refunds'
                                        ],
                                        2
                                    ) }}
                                </td>

                                <td class="advance-money">
                                    PKR
                                    {{ number_format(
                                        $row[
                                            'balance_amount'
                                        ],
                                        2
                                    ) }}
                                </td>

                                <td class="advance-nowrap">
                                    <span
                                        class="advance-badge advance-{{ $row['balance_status_key'] }}"
                                    >
                                        {{ $row[
                                            'balance_status'
                                        ] }}
                                    </span>
                                </td>

                                <td class="advance-nowrap">
                                    {{ $row[
                                        'last_advance_date'
                                    ] }}
                                </td>

                                <td class="advance-nowrap">
                                    {{ $row[
                                        'last_refund_date'
                                    ] }}
                                </td>

                                <td class="advance-nowrap">
                                    {{ $row[
                                        'customer_status'
                                    ] }}
                                </td>

                                <td class="advance-nowrap">
                                    <a
                                        href="{{ $row['customer_url'] }}"
                                        class="advance-open"
                                    >
                                        Open Customer
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="13"
                                    class="advance-empty"
                                >
                                    Selected filters ke liye
                                    koi Customer Advance balance
                                    record nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>