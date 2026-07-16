<x-filament-panels::page>
    @php
        $report = $this->reportData();
        $stats = $report['stats'];
        $bankAccountOptions =
            $this->bankAccountOptions();
    @endphp

    <style>
        .bank-rec-page {
            display: grid;
            gap: 1.25rem;
        }

        .bank-rec-panel,
        .bank-rec-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .dark .bank-rec-panel,
        .dark .bank-rec-card {
            background: #111827;
            border-color: #374151;
        }

        .bank-rec-panel {
            padding: 1.25rem;
        }

        .bank-rec-title {
            margin: 0;
            color: #111827;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .dark .bank-rec-title {
            color: #f9fafb;
        }

        .bank-rec-description {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .84rem;
        }

        .dark .bank-rec-description {
            color: #9ca3af;
        }

        .bank-rec-filters {
            display: grid;
            grid-template-columns:
                2fr 1fr 1fr auto;
            gap: 1rem;
            margin-top: 1rem;
            align-items: end;
        }

        .bank-rec-field label {
            display: block;
            margin-bottom: .4rem;
            color: #374151;
            font-size: .78rem;
            font-weight: 700;
        }

        .dark .bank-rec-field label {
            color: #d1d5db;
        }

        .bank-rec-field input,
        .bank-rec-field select {
            width: 100%;
            min-height: 2.6rem;
            padding: .55rem .75rem;
            border: 1px solid #d1d5db;
            border-radius: .65rem;
            background: white;
            color: #111827;
        }

        .dark .bank-rec-field input,
        .dark .bank-rec-field select {
            background: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .bank-rec-buttons {
            display: flex;
            gap: .5rem;
        }

        .bank-rec-button {
            min-height: 2.6rem;
            border: 0;
            border-radius: .65rem;
            padding: .55rem .9rem;
            cursor: pointer;
            font-size: .82rem;
            font-weight: 700;
        }

        .bank-rec-primary {
            background: #0f766e;
            color: white;
        }

        .bank-rec-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .dark .bank-rec-secondary {
            background: #374151;
            color: #f9fafb;
        }

        .bank-rec-stats {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .bank-rec-card {
            padding: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .bank-rec-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #0f766e;
        }

        .bank-rec-label {
            color: #6b7280;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .bank-rec-value {
            margin-top: .45rem;
            color: #111827;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .dark .bank-rec-value {
            color: #f9fafb;
        }

        .bank-rec-success {
            color: #15803d !important;
        }

        .bank-rec-danger {
            color: #b91c1c !important;
        }

        .bank-rec-table-wrap {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .bank-rec-table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }

        .bank-rec-table th {
            padding: .75rem;
            text-align: left;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: .72rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .bank-rec-table th {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .bank-rec-table td {
            padding: .78rem .75rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: .8rem;
            white-space: nowrap;
        }

        .dark .bank-rec-table td {
            border-color: #374151;
            color: #e5e7eb;
        }

        .bank-rec-money {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .bank-rec-badge {
            display: inline-flex;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .bank-rec-matched {
            background: #dcfce7;
            color: #166534;
        }

        .bank-rec-unmatched {
            background: #fef3c7;
            color: #92400e;
        }

        .bank-rec-ignored {
            background: #e5e7eb;
            color: #374151;
        }

        .bank-rec-empty {
            padding: 2rem !important;
            text-align: center;
            color: #6b7280 !important;
        }

        @media (max-width: 1100px) {
            .bank-rec-filters,
            .bank-rec-stats {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .bank-rec-filters,
            .bank-rec-stats {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .bank-rec-no-print {
                display: none !important;
            }

            .bank-rec-panel,
            .bank-rec-card {
                box-shadow: none;
                break-inside: avoid;
            }

            .bank-rec-table {
                min-width: 100%;
                font-size: 9px;
            }
        }
    </style>

    <div class="bank-rec-page">
        <section
            class="bank-rec-panel bank-rec-no-print"
        >
            <h2 class="bank-rec-title">
                Reconciliation Filters
            </h2>

            <p class="bank-rec-description">
                Bank account aur statement period select karein.
            </p>

            <div class="bank-rec-filters">
                <div class="bank-rec-field">
                    <label for="bankAccountId">
                        Bank Account
                    </label>

                    <select
                        id="bankAccountId"
                        wire:model.live="bankAccountId"
                    >
                        <option value="">
                            Select Bank Account
                        </option>

                        @foreach (
                            $bankAccountOptions
                            as $id => $label
                        )
                            <option value="{{ $id }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="bank-rec-field">
                    <label for="dateFrom">
                        From Date
                    </label>

                    <input
                        id="dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                    >
                </div>

                <div class="bank-rec-field">
                    <label for="dateTo">
                        To Date
                    </label>

                    <input
                        id="dateTo"
                        type="date"
                        wire:model.live="dateTo"
                    >
                </div>

                <div class="bank-rec-buttons">
                    <button
                        type="button"
                        class="bank-rec-button bank-rec-secondary"
                        wire:click="resetFilters"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="bank-rec-button bank-rec-primary"
                        onclick="window.print()"
                    >
                        Print
                    </button>
                </div>
            </div>
        </section>

        <section class="bank-rec-panel">
            <h2 class="bank-rec-title">
                {{
                    $report['bank_account']['name']
                    ?? 'Bank Reconciliation'
                }}
            </h2>

            <p class="bank-rec-description">
                Period:
                {{ $report['period']['from'] }}
                to
                {{ $report['period']['to'] }}

                @if ($report['bank_account'])
                    — GL:
                    {{ $report['bank_account']['gl_code'] }}
                    -
                    {{ $report['bank_account']['gl_name'] }}
                @endif
            </p>
        </section>

        <section class="bank-rec-stats">
            @php
                $cards = [
                    [
                        'label' => 'Statement Opening',
                        'value' => $stats['statement_opening'],
                    ],
                    [
                        'label' => 'Statement Deposits',
                        'value' => $stats['statement_deposits'],
                    ],
                    [
                        'label' => 'Statement Withdrawals',
                        'value' => $stats['statement_withdrawals'],
                    ],
                    [
                        'label' => 'Statement Closing',
                        'value' => $stats['statement_closing'],
                    ],
                    [
                        'label' => 'Book Opening',
                        'value' => $stats['book_opening'],
                    ],
                    [
                        'label' => 'Book Debit',
                        'value' => $stats['book_debit'],
                    ],
                    [
                        'label' => 'Book Credit',
                        'value' => $stats['book_credit'],
                    ],
                    [
                        'label' => 'Book Closing',
                        'value' => $stats['book_closing'],
                    ],
                ];
            @endphp

            @foreach ($cards as $card)
                <article class="bank-rec-card">
                    <div class="bank-rec-label">
                        {{ $card['label'] }}
                    </div>

                    <div class="bank-rec-value">
                        PKR
                        {{ number_format(
                            $card['value'],
                            2
                        ) }}
                    </div>
                </article>
            @endforeach

            <article class="bank-rec-card">
                <div class="bank-rec-label">
                    Difference
                </div>

                <div
                    class="bank-rec-value {{
                        abs($stats['difference']) < 0.01
                            ? 'bank-rec-success'
                            : 'bank-rec-danger'
                    }}"
                >
                    PKR
                    {{ number_format(
                        $stats['difference'],
                        2
                    ) }}
                </div>
            </article>

            <article class="bank-rec-card">
                <div class="bank-rec-label">
                    Matched Entries
                </div>

                <div class="bank-rec-value bank-rec-success">
                    {{ number_format(
                        $stats['matched_count']
                    ) }}
                </div>
            </article>

            <article class="bank-rec-card">
                <div class="bank-rec-label">
                    Unmatched Statement
                </div>

                <div class="bank-rec-value bank-rec-danger">
                    {{ number_format(
                        $stats[
                            'unmatched_statement_count'
                        ]
                    ) }}

                    <small>
                        / PKR
                        {{ number_format(
                            $stats[
                                'unmatched_statement_amount'
                            ],
                            2
                        ) }}
                    </small>
                </div>
            </article>

            <article class="bank-rec-card">
                <div class="bank-rec-label">
                    Unmatched Book Entries
                </div>

                <div class="bank-rec-value bank-rec-danger">
                    {{ number_format(
                        $stats[
                            'unmatched_book_count'
                        ]
                    ) }}

                    <small>
                        / PKR
                        {{ number_format(
                            $stats[
                                'unmatched_book_amount'
                            ],
                            2
                        ) }}
                    </small>
                </div>
            </article>
        </section>

        <section class="bank-rec-panel">
            <h2 class="bank-rec-title">
                Bank Statement Entries
            </h2>

            <p class="bank-rec-description">
                Matching Finance → Bank Statement menu se hogi.
            </p>

            <div class="bank-rec-table-wrap">
                <table class="bank-rec-table">
                    <thead>
                        <tr>
                            <th>Entry</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th>Deposit</th>
                            <th>Withdrawal</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Voucher</th>
                            <th>Matched At</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (
                            $report['statement_rows']
                            as $row
                        )
                            @php
                                $statusClass = match (
                                    $row['status']
                                ) {
                                    'Matched' =>
                                        'bank-rec-matched',

                                    'Ignored' =>
                                        'bank-rec-ignored',

                                    default =>
                                        'bank-rec-unmatched',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <strong>
                                        {{ $row['entry_no'] }}
                                    </strong>
                                </td>

                                <td>{{ $row['date'] }}</td>

                                <td>
                                    {{ $row['reference_no'] }}
                                </td>

                                <td>
                                    {{ $row['description'] }}
                                </td>

                                <td class="bank-rec-money">
                                    {{ number_format(
                                        $row['deposit'],
                                        2
                                    ) }}
                                </td>

                                <td class="bank-rec-money">
                                    {{ number_format(
                                        $row['withdrawal'],
                                        2
                                    ) }}
                                </td>

                                <td class="bank-rec-money">
                                    {{
                                        $row['running_balance']
                                        !== null
                                            ? number_format(
                                                $row[
                                                    'running_balance'
                                                ],
                                                2
                                            )
                                            : '-'
                                    }}
                                </td>

                                <td>
                                    <span
                                        class="bank-rec-badge {{ $statusClass }}"
                                    >
                                        {{ $row['status'] }}
                                    </span>
                                </td>

                                <td>{{ $row['voucher_no'] }}</td>
                                <td>{{ $row['matched_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="10"
                                    class="bank-rec-empty"
                                >
                                    Selected period me statement entries nahi hain.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bank-rec-panel">
            <h2 class="bank-rec-title">
                Unmatched GL Bank Entries
            </h2>

            <div class="bank-rec-table-wrap">
                <table class="bank-rec-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Voucher No</th>
                            <th>Voucher Type</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (
                            $report['unmatched_book_rows']
                            as $row
                        )
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['voucher_no'] }}</td>
                                <td>{{ $row['voucher_type'] }}</td>
                                <td>{{ $row['reference_no'] }}</td>
                                <td>{{ $row['description'] }}</td>

                                <td class="bank-rec-money">
                                    {{ number_format(
                                        $row['debit'],
                                        2
                                    ) }}
                                </td>

                                <td class="bank-rec-money">
                                    {{ number_format(
                                        $row['credit'],
                                        2
                                    ) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="bank-rec-empty"
                                >
                                    Selected period me koi unmatched GL entry nahi hai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>