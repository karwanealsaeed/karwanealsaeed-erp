<x-filament-panels::page>
    @php
        $report = $this->reportData();
        $stats = $report['stats'];
        $queueOptions = $this->queueOptions();

        $cards = [
            [
                'label' => 'Supplier Bills',
                'count' => $stats['supplier_bills']['count'],
                'amount' => $stats['supplier_bills']['amount'],
            ],
            [
                'label' => 'Supplier Payments',
                'count' => $stats['supplier_payments']['count'],
                'amount' => $stats['supplier_payments']['amount'],
            ],
            [
                'label' => 'Customer Receipts',
                'count' => $stats['customer_payments']['count'],
                'amount' => $stats['customer_payments']['amount'],
            ],
            [
                'label' => 'Customer Refunds',
                'count' => $stats['refunds']['count'],
                'amount' => $stats['refunds']['amount'],
            ],
            [
                'label' => 'Staff Ledger',
                'count' => $stats['staff_ledger']['count'],
                'amount' => $stats['staff_ledger']['amount'],
            ],
        ];
    @endphp

    <style>
        .approval-center {
            display: grid;
            gap: 1.25rem;
        }

        .approval-panel,
        .approval-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .dark .approval-panel,
        .dark .approval-card {
            background: #111827;
            border-color: #374151;
        }

        .approval-panel {
            padding: 1.25rem;
        }

        .approval-heading {
            margin: 0;
            color: #111827;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .dark .approval-heading {
            color: #f9fafb;
        }

        .approval-description {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .84rem;
            line-height: 1.55;
        }

        .dark .approval-description {
            color: #9ca3af;
        }

        .approval-filter-grid {
            display: grid;
            grid-template-columns:
                minmax(230px, 1fr)
                minmax(280px, 2fr)
                auto;
            gap: 1rem;
            align-items: end;
            margin-top: 1rem;
        }

        .approval-field label {
            display: block;
            margin-bottom: .4rem;
            color: #374151;
            font-size: .78rem;
            font-weight: 700;
        }

        .dark .approval-field label {
            color: #d1d5db;
        }

        .approval-field input,
        .approval-field select {
            width: 100%;
            min-height: 2.65rem;
            padding: .55rem .75rem;
            border: 1px solid #d1d5db;
            border-radius: .65rem;
            background: white;
            color: #111827;
        }

        .dark .approval-field input,
        .dark .approval-field select {
            background: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .approval-buttons {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .approval-button {
            min-height: 2.65rem;
            border: 0;
            border-radius: .65rem;
            padding: .55rem .9rem;
            cursor: pointer;
            font-size: .82rem;
            font-weight: 700;
        }

        .approval-button-primary {
            background: #0f766e;
            color: white;
        }

        .approval-button-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .dark .approval-button-secondary {
            background: #374151;
            color: #f9fafb;
        }

        .approval-summary {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .approval-card {
            padding: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .approval-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #0f766e;
        }

        .approval-card-total::before {
            background: #b91c1c;
        }

        .approval-card-label {
            color: #6b7280;
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .dark .approval-card-label {
            color: #9ca3af;
        }

        .approval-card-value {
            margin-top: .45rem;
            color: #111827;
            font-size: 1.45rem;
            font-weight: 900;
        }

        .dark .approval-card-value {
            color: #f9fafb;
        }

        .approval-card-amount {
            margin-top: .25rem;
            color: #6b7280;
            font-size: .8rem;
            font-weight: 600;
        }

        .dark .approval-card-amount {
            color: #9ca3af;
        }

        .approval-table-wrap {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .approval-table {
            width: 100%;
            min-width: 1180px;
            border-collapse: collapse;
        }

        .approval-table th {
            padding: .75rem;
            text-align: left;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: .71rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .approval-table th {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .approval-table td {
            padding: .82rem .75rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: .8rem;
            vertical-align: middle;
        }

        .dark .approval-table td {
            border-color: #374151;
            color: #e5e7eb;
        }

        .approval-table td:not(.approval-description-cell) {
            white-space: nowrap;
        }

        .approval-description-cell {
            min-width: 250px;
            max-width: 360px;
            white-space: normal;
            line-height: 1.45;
        }

        .approval-money {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
        }

        .approval-badge {
            display: inline-flex;
            align-items: center;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 800;
        }

        .approval-type {
            background: #dbeafe;
            color: #1e40af;
        }

        .approval-status {
            background: #fef3c7;
            color: #92400e;
        }

        .priority-normal {
            background: #dcfce7;
            color: #166534;
        }

        .priority-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .priority-high {
            background: #fed7aa;
            color: #9a3412;
        }

        .priority-critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .approval-open-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.2rem;
            padding: .42rem .7rem;
            border-radius: .55rem;
            background: #0f766e;
            color: white;
            font-size: .75rem;
            font-weight: 800;
            text-decoration: none;
        }

        .approval-open-link:hover {
            background: #115e59;
        }

        .approval-empty {
            padding: 2.5rem !important;
            text-align: center;
            color: #6b7280 !important;
        }

        .approval-notice {
            border-left: 4px solid #0f766e;
        }

        @media (max-width: 1050px) {
            .approval-filter-grid,
            .approval-summary {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .approval-filter-grid,
            .approval-summary {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .approval-no-print {
                display: none !important;
            }

            .approval-panel,
            .approval-card {
                box-shadow: none;
                break-inside: avoid;
            }

            .approval-table {
                min-width: 100%;
                font-size: 9px;
            }

            .approval-open-link {
                display: none !important;
            }
        }
    </style>

    <div class="approval-center">
        <section
            class="approval-panel approval-notice"
        >
            <h2 class="approval-heading">
                Central Finance Review Queue
            </h2>

            <p class="approval-description">
                Ye page tamam pending accounting records ko
                ek jagah consolidate karti hai. Actual Approve,
                Reject aur GL Posting source module par hogi,
                taake existing validation aur accounting posting
                services safely use hon.
            </p>
        </section>

        <section
            class="approval-panel approval-no-print"
        >
            <h2 class="approval-heading">
                Queue Filters
            </h2>

            <div class="approval-filter-grid">
                <div class="approval-field">
                    <label for="queueType">
                        Approval Queue
                    </label>

                    <select
                        id="queueType"
                        wire:model.live="queueType"
                    >
                        @foreach (
                            $queueOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="approval-field">
                    <label for="search">
                        Search Reference, Party or Category
                    </label>

                    <input
                        id="search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Example: SB-000001, Bilal, Visa..."
                    >
                </div>

                <div class="approval-buttons">
                    <button
                        type="button"
                        class="approval-button approval-button-secondary"
                        wire:click="resetFilters"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="approval-button approval-button-primary"
                        wire:click="$refresh"
                    >
                        Refresh
                    </button>

                    <button
                        type="button"
                        class="approval-button approval-button-primary"
                        onclick="window.print()"
                    >
                        Print
                    </button>
                </div>
            </div>
        </section>

        <section class="approval-summary">
            <article
                class="approval-card approval-card-total"
            >
                <div class="approval-card-label">
                    Total Pending Records
                </div>

                <div class="approval-card-value">
                    {{ number_format(
                        $stats['total_pending']
                    ) }}
                </div>

                <div class="approval-card-amount">
                    Filtered:
                    {{ number_format(
                        $stats['filtered_count']
                    ) }}
                </div>
            </article>

            @foreach ($cards as $card)
                <article class="approval-card">
                    <div class="approval-card-label">
                        {{ $card['label'] }}
                    </div>

                    <div class="approval-card-value">
                        {{ number_format(
                            $card['count']
                        ) }}
                    </div>

                    <div class="approval-card-amount">
                        Queue Value:
                        PKR
                        {{ number_format(
                            $card['amount'],
                            2
                        ) }}
                    </div>
                </article>
            @endforeach
        </section>

        <section class="approval-panel">
            <h2 class="approval-heading">
                Pending Approval Records
            </h2>

            <p class="approval-description">
                Generated:
                {{ $report['generated_at'] }}
            </p>

            <div class="approval-table-wrap">
                <table class="approval-table">
                    <thead>
                        <tr>
                            <th>Queue</th>
                            <th>Reference</th>
                            <th>Party / Staff</th>
                            <th>Date</th>
                            <th>Age</th>
                            <th>Priority</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Review</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (
                            $report['queue_rows']
                            as $row
                        )
                            <tr>
                                <td>
                                    <span
                                        class="approval-badge approval-type"
                                    >
                                        {{ $row['queue_label'] }}
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        {{ $row['source_no'] }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $row['party'] }}
                                </td>

                                <td>
                                    {{ $row['date'] }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['age_days']
                                    ) }}
                                    Days
                                </td>

                                <td>
                                    <span
                                        class="approval-badge priority-{{ $row['priority_key'] }}"
                                    >
                                        {{ $row['priority'] }}
                                    </span>
                                </td>

                                <td>
                                    {{ $row['category'] }}
                                </td>

                                <td
                                    class="approval-description-cell"
                                >
                                    {{ $row['description'] }}
                                </td>

                                <td class="approval-money">
                                    PKR
                                    {{ number_format(
                                        $row['amount'],
                                        2
                                    ) }}
                                </td>

                                <td>
                                    <span
                                        class="approval-badge approval-status"
                                    >
                                        {{ $row['status'] }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="{{ $row['url'] }}"
                                        class="approval-open-link"
                                    >
                                        Open & Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="11"
                                    class="approval-empty"
                                >
                                    Selected filters ke liye koi
                                    pending approval record nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>