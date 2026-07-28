<x-filament-panels::page>
    @php
        $report = $this->reportData();
        $stats = $report['stats'];

        $customerOptions = $this->customerOptions();
        $customerTypeOptions =
            $this->customerTypeOptions();
        $serviceTypeOptions =
            $this->serviceTypeOptions();
    @endphp

    <style>
        .aging-page {
            display: grid;
            gap: 1.25rem;
        }

        .aging-panel,
        .aging-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .dark .aging-panel,
        .dark .aging-card {
            background: #111827;
            border-color: #374151;
        }

        .aging-panel {
            padding: 1.25rem;
        }

        .aging-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
        }

        .dark .aging-title {
            color: #f9fafb;
        }

        .aging-description {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .85rem;
        }

        .dark .aging-description {
            color: #9ca3af;
        }

        .aging-filters {
            display: grid;
            grid-template-columns: repeat(
                4,
                minmax(0, 1fr)
            );
            gap: 1rem;
            margin-top: 1rem;
            align-items: end;
        }

        .aging-field label {
            display: block;
            margin-bottom: .4rem;
            color: #374151;
            font-size: .78rem;
            font-weight: 700;
        }

        .dark .aging-field label {
            color: #d1d5db;
        }

        .aging-field select {
            width: 100%;
            min-height: 2.6rem;
            padding: .55rem .75rem;
            border: 1px solid #d1d5db;
            border-radius: .65rem;
            background: white;
            color: #111827;
        }

        .dark .aging-field select {
            background: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .aging-buttons {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .aging-button {
            min-height: 2.6rem;
            border: 0;
            border-radius: .65rem;
            padding: .55rem .9rem;
            cursor: pointer;
            font-size: .82rem;
            font-weight: 700;
        }

        .aging-button-primary {
            background: #0f766e;
            color: white;
        }

        .aging-button-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .dark .aging-button-secondary {
            background: #374151;
            color: #f9fafb;
        }

        .aging-stats {
            display: grid;
            grid-template-columns: repeat(
                4,
                minmax(0, 1fr)
            );
            gap: 1rem;
        }

        .aging-card {
            padding: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .aging-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #0f766e;
        }

        .aging-stat-label {
            color: #6b7280;
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .dark .aging-stat-label {
            color: #9ca3af;
        }

        .aging-stat-value {
            margin-top: .45rem;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 800;
        }

        .dark .aging-stat-value {
            color: #f9fafb;
        }

        .aging-table-wrap {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .aging-table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
        }

        .aging-table th {
            padding: .75rem;
            text-align: left;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: .72rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .aging-table th {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .aging-table td {
            padding: .78rem .75rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: .8rem;
            white-space: nowrap;
        }

        .dark .aging-table td {
            border-color: #374151;
            color: #e5e7eb;
        }

        .aging-badge {
            display: inline-flex;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .aging-current {
            background: #dbeafe;
            color: #1e40af;
        }

        .aging-1-30 {
            background: #fef3c7;
            color: #92400e;
        }

        .aging-31-60 {
            background: #fed7aa;
            color: #9a3412;
        }

        .aging-61-90 {
            background: #fecaca;
            color: #991b1b;
        }

        .aging-90-plus {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .aging-empty {
            padding: 2rem !important;
            text-align: center;
            color: #6b7280 !important;
        }

        .aging-money {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .aging-total {
            font-weight: 800;
            color: #b91c1c !important;
        }

        @media (max-width: 1100px) {
            .aging-filters {
                grid-template-columns: repeat(
                    2,
                    minmax(0, 1fr)
                );
            }

            .aging-stats {
                grid-template-columns: repeat(
                    2,
                    minmax(0, 1fr)
                );
            }
        }

        @media (max-width: 650px) {
            .aging-filters,
            .aging-stats {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .aging-no-print {
                display: none !important;
            }

            .aging-panel,
            .aging-card {
                box-shadow: none;
                break-inside: avoid;
            }

            .aging-table {
                min-width: 100%;
                font-size: 9px;
            }
        }
    </style>

    <div class="aging-page">
        <section class="aging-panel aging-no-print">
            <h2 class="aging-title">
                Aging Filters
            </h2>

            <p class="aging-description">
                Current outstanding invoice balances only.
                Cancelled, Draft aur Paid invoices exclude hain.
            </p>

            <div class="aging-filters">
                <div class="aging-field">
                    <label for="customerId">
                        Customer / Agent
                    </label>

                    <select
                        id="customerId"
                        wire:model.live="customerId"
                    >
                        <option value="">
                            All Customers & Agents
                        </option>

                        @foreach (
                            $customerOptions as $id => $label
                        )
                            <option value="{{ $id }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="aging-field">
                    <label for="customerType">
                        Customer Type
                    </label>

                    <select
                        id="customerType"
                        wire:model.live="customerType"
                    >
                        <option value="">
                            All Types
                        </option>

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

                <div class="aging-field">
                    <label for="serviceType">
                        Service Type
                    </label>

                    <select
                        id="serviceType"
                        wire:model.live="serviceType"
                    >
                        <option value="">
                            All Services
                        </option>

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

                <div class="aging-buttons">
                    <button
                        type="button"
                        class="aging-button aging-button-secondary"
                        wire:click="resetFilters"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="aging-button aging-button-primary"
                        data-kes-final-four-print
                    >
                        Print Report
                    </button>
                </div>
            </div>
        </section>

        <section class="aging-panel">
            <h2 class="aging-title">
                Customer Outstanding Summary
            </h2>

            <p class="aging-description">
                Aging date:
                <strong>{{ $report['aging_date'] }}</strong>

                — Due date missing ho to invoice date use hoti hai.
            </p>
        </section>

        <section class="aging-stats">
            <article class="aging-card">
                <div class="aging-stat-label">
                    Outstanding Customers
                </div>

                <div class="aging-stat-value">
                    {{ number_format(
                        $stats['customer_count']
                    ) }}
                </div>
            </article>

            <article class="aging-card">
                <div class="aging-stat-label">
                    Outstanding Invoices
                </div>

                <div class="aging-stat-value">
                    {{ number_format(
                        $stats['invoice_count']
                    ) }}
                </div>
            </article>

            <article class="aging-card">
                <div class="aging-stat-label">
                    Total Outstanding
                </div>

                <div class="aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['total_outstanding'],
                        2
                    ) }}
                </div>
            </article>

            <article class="aging-card">
                <div class="aging-stat-label">
                    Current / Not Due
                </div>

                <div class="aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['current'],
                        2
                    ) }}
                </div>
            </article>

            <article class="aging-card">
                <div class="aging-stat-label">
                    1–30 Days
                </div>

                <div class="aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['days_1_30'],
                        2
                    ) }}
                </div>
            </article>

            <article class="aging-card">
                <div class="aging-stat-label">
                    31–60 Days
                </div>

                <div class="aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['days_31_60'],
                        2
                    ) }}
                </div>
            </article>

            <article class="aging-card">
                <div class="aging-stat-label">
                    61–90 Days
                </div>

                <div class="aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['days_61_90'],
                        2
                    ) }}
                </div>
            </article>

            <article class="aging-card">
                <div class="aging-stat-label">
                    Over 90 Days
                </div>

                <div class="aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['days_90_plus'],
                        2
                    ) }}
                </div>
            </article>
        </section>

        <section class="aging-panel">
            <h2 class="aging-title">
                Customer-wise Aging
            </h2>

            <div class="aging-table-wrap">
                <table class="aging-table">
                    <thead>
                        <tr>
                            <th>Customer / Agent</th>
                            <th>Type</th>
                            <th>Mobile</th>
                            <th>Invoices</th>
                            <th>Oldest Due</th>
                            <th>Maximum Days</th>
                            <th>Current</th>
                            <th>1–30</th>
                            <th>31–60</th>
                            <th>61–90</th>
                            <th>90+</th>
                            <th>Total Outstanding</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (
                            $report['customer_rows']
                            as $row
                        )
                            <tr>
                                <td>
                                    <strong>
                                        {{ $row['customer_name'] }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $row['customer_type'] }}
                                </td>

                                <td>
                                    {{ $row['customer_mobile'] ?: '-' }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['invoice_count']
                                    ) }}
                                </td>

                                <td>
                                    {{ $row['oldest_due_date'] }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row[
                                            'maximum_days_overdue'
                                        ]
                                    ) }}
                                </td>

                                <td class="aging-money">
                                    {{ number_format(
                                        $row['current'],
                                        2
                                    ) }}
                                </td>

                                <td class="aging-money">
                                    {{ number_format(
                                        $row['days_1_30'],
                                        2
                                    ) }}
                                </td>

                                <td class="aging-money">
                                    {{ number_format(
                                        $row['days_31_60'],
                                        2
                                    ) }}
                                </td>

                                <td class="aging-money">
                                    {{ number_format(
                                        $row['days_61_90'],
                                        2
                                    ) }}
                                </td>

                                <td class="aging-money">
                                    {{ number_format(
                                        $row['days_90_plus'],
                                        2
                                    ) }}
                                </td>

                                <td class="aging-money aging-total">
                                    PKR
                                    {{ number_format(
                                        $row[
                                            'total_outstanding'
                                        ],
                                        2
                                    ) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="12"
                                    class="aging-empty"
                                >
                                    Koi outstanding customer invoice nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="aging-panel">
            <h2 class="aging-title">
                Invoice-wise Aging Details
            </h2>

            <div class="aging-table-wrap">
                <table class="aging-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Service</th>
                            <th>Reference</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Bucket</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (
                            $report['invoice_rows']
                            as $row
                        )
                            @php
                                $bucketClass = match (
                                    $row['bucket']
                                ) {
                                    'Current' =>
                                        'aging-current',

                                    '1-30 Days' =>
                                        'aging-1-30',

                                    '31-60 Days' =>
                                        'aging-31-60',

                                    '61-90 Days' =>
                                        'aging-61-90',

                                    default =>
                                        'aging-90-plus',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <strong>
                                        {{ $row['invoice_no'] }}
                                    </strong>
                                </td>

                                <td>{{ $row['customer_name'] }}</td>
                                <td>{{ $row['customer_type'] }}</td>
                                <td>{{ $row['service_type'] }}</td>

                                <td>
                                    {{ $row['service_reference'] ?: '-' }}
                                </td>

                                <td>{{ $row['invoice_date'] }}</td>
                                <td>{{ $row['due_date'] }}</td>

                                <td>
                                    {{ number_format(
                                        $row['days_overdue']
                                    ) }}
                                </td>

                                <td>
                                    <span
                                        class="aging-badge {{ $bucketClass }}"
                                    >
                                        {{ $row['bucket'] }}
                                    </span>
                                </td>

                                <td class="aging-money">
                                    {{ number_format(
                                        $row['total_amount'],
                                        2
                                    ) }}
                                </td>

                                <td class="aging-money">
                                    {{ number_format(
                                        $row['paid_amount'],
                                        2
                                    ) }}
                                </td>

                                <td class="aging-money aging-total">
                                    PKR
                                    {{ number_format(
                                        $row['balance_amount'],
                                        2
                                    ) }}
                                </td>

                                <td>{{ $row['status'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="13"
                                    class="aging-empty"
                                >
                                    Selected filters ke liye outstanding invoice nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- PHASE 16F-3D7 CUSTOMER AGING LANDSCAPE PRINT FIX START --}}
    <style>
        /*
         * Screen containment: only report tables may scroll horizontally.
         */
        .aging-page {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            overflow-x: clip !important;
        }

        .aging-page > *,
        .aging-panel,
        .aging-filters,
        .aging-stats,
        .aging-table-wrap {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .aging-table-wrap {
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
            overscroll-behavior-inline: contain;
        }

        .aging-table {
            width: 100% !important;
            max-width: none !important;
        }

        /*
         * This report contains wide aging columns and two detail tables.
         * Force a compact A4 landscape print contract after its older local
         * print styles, so the browser preview no longer defaults to Portrait.
         */
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 7mm;
            }

            html,
            body,
            body.fi-body,
            .fi-body {
                width: 100% !important;
                min-width: 0 !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                background: #fff !important;
            }

            .aging-page {
                width: 100% !important;
                min-width: 0 !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                color: #111 !important;
                background: #fff !important;
            }

            .aging-page :where(
                .aging-no-print,
                .aging-filters,
                .aging-buttons,
                [data-karwan-report-primary-actions],
                [data-karwan-export-actions],
                .kes-report-export-actions,
                .kes-print-action,
                button
            ) {
                display: none !important;
            }

            .aging-panel {
                width: 100% !important;
                min-width: 0 !important;
                max-width: none !important;
                margin: 0 0 3mm !important;
                padding: 3mm !important;
                overflow: visible !important;
                border: 1px solid #d7ddd9 !important;
                border-radius: 3mm !important;
                box-shadow: none !important;
                background: #fff !important;
            }

            .aging-title {
                margin: 0 0 1.5mm !important;
                font-size: 14pt !important;
                line-height: 1.15 !important;
            }

            .aging-description {
                margin: 0 !important;
                font-size: 8pt !important;
                line-height: 1.25 !important;
            }

            /*
             * Six summary cards use four compact columns in landscape.
             * This prevents the cards from consuming a full portrait page.
             */
            .aging-stats {
                display: grid !important;
                width: 100% !important;
                grid-template-columns:
                    repeat(4, minmax(0, 1fr)) !important;
                gap: 2.5mm !important;
                margin: 0 0 3mm !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .aging-card {
                min-width: 0 !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 2.5mm 3mm !important;
                border-radius: 2.5mm !important;
                box-shadow: none !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .aging-stat-label {
                margin: 0 0 1mm !important;
                font-size: 6.8pt !important;
                line-height: 1.15 !important;
            }

            .aging-stat-value {
                margin: 0 !important;
                font-size: 11pt !important;
                line-height: 1.1 !important;
            }

            .aging-table-wrap {
                display: block !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: none !important;
                height: auto !important;
                max-height: none !important;
                margin: 0 !important;
                overflow: visible !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .aging-table {
                display: table !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
                table-layout: auto !important;
                border-collapse: collapse !important;
                font-size: 7pt !important;
            }

            .aging-table thead {
                display: table-header-group !important;
            }

            .aging-table tfoot {
                display: table-footer-group !important;
            }

            .aging-table tr {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .aging-table th,
            .aging-table td {
                min-width: 0 !important;
                max-width: none !important;
                padding: 1.5mm 1.7mm !important;
                white-space: normal !important;
                overflow: visible !important;
                overflow-wrap: anywhere !important;
                word-break: normal !important;
                text-overflow: clip !important;
                vertical-align: top !important;
            }

            .aging-badge {
                display: inline-block !important;
                max-width: 100% !important;
                padding: .6mm 1.2mm !important;
                font-size: 6.5pt !important;
                white-space: normal !important;
            }

            .aging-money {
                white-space: nowrap !important;
            }

            .aging-empty {
                padding: 3mm !important;
                font-size: 8pt !important;
            }
        }
    </style>
    {{-- PHASE 16F-3D7 CUSTOMER AGING LANDSCAPE PRINT FIX END --}}
</x-filament-panels::page>