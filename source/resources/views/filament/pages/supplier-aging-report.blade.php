<x-filament-panels::page>
    @php
        $report = $this->reportData();
        $stats = $report['stats'];

        $supplierOptions =
            $this->supplierOptions();

        $supplierTypeOptions =
            $this->supplierTypeOptions();

        $serviceTypeOptions =
            $this->serviceTypeOptions();
    @endphp

    <style>
        .supplier-aging-page {
            display: grid;
            gap: 1.25rem;
        }

        .supplier-aging-panel,
        .supplier-aging-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .dark .supplier-aging-panel,
        .dark .supplier-aging-card {
            background: #111827;
            border-color: #374151;
        }

        .supplier-aging-panel {
            padding: 1.25rem;
        }

        .supplier-aging-title {
            margin: 0;
            color: #111827;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .dark .supplier-aging-title {
            color: #f9fafb;
        }

        .supplier-aging-description {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .85rem;
        }

        .dark .supplier-aging-description {
            color: #9ca3af;
        }

        .supplier-aging-filters {
            display: grid;
            grid-template-columns: repeat(
                4,
                minmax(0, 1fr)
            );
            gap: 1rem;
            margin-top: 1rem;
            align-items: end;
        }

        .supplier-aging-field label {
            display: block;
            margin-bottom: .4rem;
            color: #374151;
            font-size: .78rem;
            font-weight: 700;
        }

        .dark .supplier-aging-field label {
            color: #d1d5db;
        }

        .supplier-aging-field select {
            width: 100%;
            min-height: 2.6rem;
            padding: .55rem .75rem;
            border: 1px solid #d1d5db;
            border-radius: .65rem;
            background: white;
            color: #111827;
        }

        .dark .supplier-aging-field select {
            background: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .supplier-aging-buttons {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .supplier-aging-button {
            min-height: 2.6rem;
            border: 0;
            border-radius: .65rem;
            padding: .55rem .9rem;
            cursor: pointer;
            font-size: .82rem;
            font-weight: 700;
        }

        .supplier-aging-primary {
            background: #0f766e;
            color: white;
        }

        .supplier-aging-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .dark .supplier-aging-secondary {
            background: #374151;
            color: #f9fafb;
        }

        .supplier-aging-stats {
            display: grid;
            grid-template-columns: repeat(
                4,
                minmax(0, 1fr)
            );
            gap: 1rem;
        }

        .supplier-aging-card {
            padding: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .supplier-aging-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #0f766e;
        }

        .supplier-aging-stat-label {
            color: #6b7280;
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .dark .supplier-aging-stat-label {
            color: #9ca3af;
        }

        .supplier-aging-stat-value {
            margin-top: .45rem;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 800;
        }

        .dark .supplier-aging-stat-value {
            color: #f9fafb;
        }

        .supplier-aging-table-wrap {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .supplier-aging-table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
        }

        .supplier-aging-table th {
            padding: .75rem;
            text-align: left;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: .72rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .supplier-aging-table th {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .supplier-aging-table td {
            padding: .78rem .75rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: .8rem;
            white-space: nowrap;
        }

        .dark .supplier-aging-table td {
            border-color: #374151;
            color: #e5e7eb;
        }

        .supplier-aging-badge {
            display: inline-flex;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .supplier-aging-current {
            background: #dbeafe;
            color: #1e40af;
        }

        .supplier-aging-1-30 {
            background: #fef3c7;
            color: #92400e;
        }

        .supplier-aging-31-60 {
            background: #fed7aa;
            color: #9a3412;
        }

        .supplier-aging-61-90 {
            background: #fecaca;
            color: #991b1b;
        }

        .supplier-aging-90-plus {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .supplier-aging-money {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .supplier-aging-total {
            color: #b91c1c !important;
            font-weight: 800;
        }

        .supplier-aging-empty {
            padding: 2rem !important;
            text-align: center;
            color: #6b7280 !important;
        }

        @media (max-width: 1100px) {
            .supplier-aging-filters,
            .supplier-aging-stats {
                grid-template-columns: repeat(
                    2,
                    minmax(0, 1fr)
                );
            }
        }

        @media (max-width: 650px) {
            .supplier-aging-filters,
            .supplier-aging-stats {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .supplier-aging-no-print {
                display: none !important;
            }

            .supplier-aging-panel,
            .supplier-aging-card {
                box-shadow: none;
                break-inside: avoid;
            }

            .supplier-aging-table {
                min-width: 100%;
                font-size: 9px;
            }
        }
    </style>

    <div class="supplier-aging-page">
        <section
            class="supplier-aging-panel supplier-aging-no-print"
        >
            <h2 class="supplier-aging-title">
                Aging Filters
            </h2>

            <p class="supplier-aging-description">
                Sirf Unpaid aur Partially Paid supplier
                bills include honge.
            </p>

            <div class="supplier-aging-filters">
                <div class="supplier-aging-field">
                    <label for="supplierId">
                        Supplier
                    </label>

                    <select
                        id="supplierId"
                        wire:model.live="supplierId"
                    >
                        <option value="">
                            All Suppliers
                        </option>

                        @foreach (
                            $supplierOptions
                            as $id => $label
                        )
                            <option value="{{ $id }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="supplier-aging-field">
                    <label for="supplierType">
                        Supplier Type
                    </label>

                    <select
                        id="supplierType"
                        wire:model.live="supplierType"
                    >
                        <option value="">
                            All Supplier Types
                        </option>

                        @foreach (
                            $supplierTypeOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="supplier-aging-field">
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

                <div class="supplier-aging-buttons">
                    <button
                        type="button"
                        class="supplier-aging-button supplier-aging-secondary"
                        wire:click="resetFilters"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="supplier-aging-button supplier-aging-primary"
                        onclick="window.print()"
                    >
                        Print Report
                    </button>
                </div>
            </div>
        </section>

        <section class="supplier-aging-panel">
            <h2 class="supplier-aging-title">
                Supplier Outstanding Summary
            </h2>

            <p class="supplier-aging-description">
                Aging date:
                <strong>
                    {{ $report['aging_date'] }}
                </strong>

                — Due date missing ho to bill date use hoti hai.
            </p>
        </section>

        <section class="supplier-aging-stats">
            <article class="supplier-aging-card">
                <div class="supplier-aging-stat-label">
                    Outstanding Suppliers
                </div>

                <div class="supplier-aging-stat-value">
                    {{ number_format(
                        $stats['supplier_count']
                    ) }}
                </div>
            </article>

            <article class="supplier-aging-card">
                <div class="supplier-aging-stat-label">
                    Outstanding Bills
                </div>

                <div class="supplier-aging-stat-value">
                    {{ number_format(
                        $stats['bill_count']
                    ) }}
                </div>
            </article>

            <article class="supplier-aging-card">
                <div class="supplier-aging-stat-label">
                    Total Outstanding
                </div>

                <div class="supplier-aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['total_outstanding'],
                        2
                    ) }}
                </div>
            </article>

            <article class="supplier-aging-card">
                <div class="supplier-aging-stat-label">
                    Current / Not Due
                </div>

                <div class="supplier-aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['current'],
                        2
                    ) }}
                </div>
            </article>

            <article class="supplier-aging-card">
                <div class="supplier-aging-stat-label">
                    1–30 Days
                </div>

                <div class="supplier-aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['days_1_30'],
                        2
                    ) }}
                </div>
            </article>

            <article class="supplier-aging-card">
                <div class="supplier-aging-stat-label">
                    31–60 Days
                </div>

                <div class="supplier-aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['days_31_60'],
                        2
                    ) }}
                </div>
            </article>

            <article class="supplier-aging-card">
                <div class="supplier-aging-stat-label">
                    61–90 Days
                </div>

                <div class="supplier-aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['days_61_90'],
                        2
                    ) }}
                </div>
            </article>

            <article class="supplier-aging-card">
                <div class="supplier-aging-stat-label">
                    Over 90 Days
                </div>

                <div class="supplier-aging-stat-value">
                    PKR
                    {{ number_format(
                        $stats['days_90_plus'],
                        2
                    ) }}
                </div>
            </article>
        </section>

        <section class="supplier-aging-panel">
            <h2 class="supplier-aging-title">
                Supplier-wise Aging
            </h2>

            <div class="supplier-aging-table-wrap">
                <table class="supplier-aging-table">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Type</th>
                            <th>Mobile</th>
                            <th>Bills</th>
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
                            $report['supplier_rows']
                            as $row
                        )
                            <tr>
                                <td>
                                    <strong>
                                        {{ $row['supplier_name'] }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $row['supplier_type'] }}
                                </td>

                                <td>
                                    {{ $row['supplier_mobile'] ?: '-' }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['bill_count']
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

                                <td class="supplier-aging-money">
                                    {{ number_format(
                                        $row['current'],
                                        2
                                    ) }}
                                </td>

                                <td class="supplier-aging-money">
                                    {{ number_format(
                                        $row['days_1_30'],
                                        2
                                    ) }}
                                </td>

                                <td class="supplier-aging-money">
                                    {{ number_format(
                                        $row['days_31_60'],
                                        2
                                    ) }}
                                </td>

                                <td class="supplier-aging-money">
                                    {{ number_format(
                                        $row['days_61_90'],
                                        2
                                    ) }}
                                </td>

                                <td class="supplier-aging-money">
                                    {{ number_format(
                                        $row['days_90_plus'],
                                        2
                                    ) }}
                                </td>

                                <td
                                    class="supplier-aging-money supplier-aging-total"
                                >
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
                                    class="supplier-aging-empty"
                                >
                                    Koi outstanding supplier bill nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="supplier-aging-panel">
            <h2 class="supplier-aging-title">
                Bill-wise Aging Details
            </h2>

            <div class="supplier-aging-table-wrap">
                <table class="supplier-aging-table">
                    <thead>
                        <tr>
                            <th>Bill No</th>
                            <th>Supplier</th>
                            <th>Supplier Invoice</th>
                            <th>Service</th>
                            <th>Reference</th>
                            <th>Bill Date</th>
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
                            $report['bill_rows']
                            as $row
                        )
                            @php
                                $bucketClass = match (
                                    $row['bucket']
                                ) {
                                    'Current' =>
                                        'supplier-aging-current',

                                    '1-30 Days' =>
                                        'supplier-aging-1-30',

                                    '31-60 Days' =>
                                        'supplier-aging-31-60',

                                    '61-90 Days' =>
                                        'supplier-aging-61-90',

                                    default =>
                                        'supplier-aging-90-plus',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <strong>
                                        {{ $row['bill_no'] }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $row['supplier_name'] }}
                                </td>

                                <td>
                                    {{
                                        $row[
                                            'supplier_invoice_no'
                                        ] ?: '-'
                                    }}
                                </td>

                                <td>
                                    {{ $row['service_type'] }}
                                </td>

                                <td>
                                    {{ $row['reference_no'] ?: '-' }}
                                </td>

                                <td>
                                    {{ $row['bill_date'] }}
                                </td>

                                <td>
                                    {{ $row['due_date'] }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['days_overdue']
                                    ) }}
                                </td>

                                <td>
                                    <span
                                        class="supplier-aging-badge {{ $bucketClass }}"
                                    >
                                        {{ $row['bucket'] }}
                                    </span>
                                </td>

                                <td class="supplier-aging-money">
                                    {{ number_format(
                                        $row['total_amount'],
                                        2
                                    ) }}
                                </td>

                                <td class="supplier-aging-money">
                                    {{ number_format(
                                        $row['paid_amount'],
                                        2
                                    ) }}
                                </td>

                                <td
                                    class="supplier-aging-money supplier-aging-total"
                                >
                                    PKR
                                    {{ number_format(
                                        $row['balance_amount'],
                                        2
                                    ) }}
                                </td>

                                <td>
                                    {{ $row['status'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="13"
                                    class="supplier-aging-empty"
                                >
                                    Selected filters ke liye outstanding bill nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>