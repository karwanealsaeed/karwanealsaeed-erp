<x-filament-panels::page>
    @php
        $report = $this->reportData();
        $stats = $report['stats'];

        $voucherStatusOptions =
            $this->voucherStatusOptions();

        $severityOptions =
            $this->severityOptions();

        $issueTypeOptions =
            $this->issueTypeOptions();
    @endphp

    <style>
        .gl-audit {
            display: grid;
            gap: 1.25rem;
        }

        .gl-panel,
        .gl-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .dark .gl-panel,
        .dark .gl-card {
            background: #111827;
            border-color: #374151;
        }

        .gl-panel {
            padding: 1.25rem;
        }

        .gl-title {
            margin: 0;
            color: #111827;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .dark .gl-title {
            color: #f9fafb;
        }

        .gl-description {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .84rem;
            line-height: 1.55;
        }

        .dark .gl-description {
            color: #9ca3af;
        }

        .gl-notice {
            border-left: 4px solid #0f766e;
        }

        .gl-filters {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .gl-field label {
            display: block;
            margin-bottom: .4rem;
            color: #374151;
            font-size: .76rem;
            font-weight: 700;
        }

        .dark .gl-field label {
            color: #d1d5db;
        }

        .gl-field input,
        .gl-field select {
            width: 100%;
            min-height: 2.65rem;
            padding: .55rem .75rem;
            border: 1px solid #d1d5db;
            border-radius: .65rem;
            background: white;
            color: #111827;
        }

        .dark .gl-field input,
        .dark .gl-field select {
            background: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .gl-buttons {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            align-items: end;
        }

        .gl-button {
            min-height: 2.65rem;
            border: 0;
            border-radius: .65rem;
            padding: .55rem .9rem;
            cursor: pointer;
            font-size: .8rem;
            font-weight: 700;
        }

        .gl-button-primary {
            background: #0f766e;
            color: white;
        }

        .gl-button-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .dark .gl-button-secondary {
            background: #374151;
            color: #f9fafb;
        }

        .gl-stats {
            display: grid;
            grid-template-columns:
                repeat(5, minmax(0, 1fr));
            gap: 1rem;
        }

        .gl-card {
            padding: 1.05rem;
            position: relative;
            overflow: hidden;
        }

        .gl-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #0f766e;
        }

        .gl-card-critical::before {
            background: #b91c1c;
        }

        .gl-card-warning::before {
            background: #d97706;
        }

        .gl-card-label {
            color: #6b7280;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .dark .gl-card-label {
            color: #9ca3af;
        }

        .gl-card-value {
            margin-top: .4rem;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 900;
        }

        .dark .gl-card-value {
            color: #f9fafb;
        }

        .gl-success {
            color: #15803d !important;
        }

        .gl-danger {
            color: #b91c1c !important;
        }

        .gl-table-wrap {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .gl-table {
            width: 100%;
            min-width: 1450px;
            border-collapse: collapse;
        }

        .gl-table th {
            padding: .72rem;
            text-align: left;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: .69rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .gl-table th {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .gl-table td {
            padding: .8rem .72rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: .78rem;
            vertical-align: top;
        }

        .dark .gl-table td {
            border-color: #374151;
            color: #e5e7eb;
        }

        .gl-nowrap {
            white-space: nowrap;
        }

        .gl-text-cell {
            min-width: 250px;
            max-width: 370px;
            line-height: 1.45;
        }

        .gl-money {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
        }

        .gl-badge {
            display: inline-flex;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .gl-critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .gl-high {
            background: #ffedd5;
            color: #9a3412;
        }

        .gl-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .gl-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .gl-status {
            background: #e5e7eb;
            color: #374151;
        }

        .gl-open {
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

        .gl-empty {
            padding: 2.5rem !important;
            text-align: center;
            color: #6b7280 !important;
        }

        @media (max-width: 1150px) {
            .gl-stats {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

            .gl-filters {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .gl-stats,
            .gl-filters {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .gl-no-print {
                display: none !important;
            }

            .gl-panel,
            .gl-card {
                box-shadow: none;
                break-inside: avoid;
            }

            .gl-table {
                min-width: 100%;
                font-size: 8px;
            }

            .gl-open {
                display: none;
            }
        }
    </style>

    <div class="gl-audit">
        <section class="gl-panel gl-notice">
            <h2 class="gl-title">
                Read-Only Accounting Integrity Audit
            </h2>

            <p class="gl-description">
                Ye report koi voucher, line ya ledger record
                modify nahi karti. Critical issue milne par
                direct database editing ki bajaye controlled
                reversal aur corrected voucher workflow use karein.
            </p>
        </section>

        <section class="gl-panel gl-no-print">
            <h2 class="gl-title">
                Audit Filters
            </h2>

            <div class="gl-filters">
                <div class="gl-field">
                    <label for="dateFrom">
                        From Date
                    </label>

                    <input
                        id="dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                    >
                </div>

                <div class="gl-field">
                    <label for="dateTo">
                        To Date
                    </label>

                    <input
                        id="dateTo"
                        type="date"
                        wire:model.live="dateTo"
                    >
                </div>

                <div class="gl-field">
                    <label for="voucherStatus">
                        Voucher Status
                    </label>

                    <select
                        id="voucherStatus"
                        wire:model.live="voucherStatus"
                    >
                        @foreach (
                            $voucherStatusOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="gl-field">
                    <label for="severity">
                        Severity
                    </label>

                    <select
                        id="severity"
                        wire:model.live="severity"
                    >
                        @foreach (
                            $severityOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="gl-field">
                    <label for="issueType">
                        Issue Type
                    </label>

                    <select
                        id="issueType"
                        wire:model.live="issueType"
                    >
                        @foreach (
                            $issueTypeOptions
                            as $value => $label
                        )
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="gl-field">
                    <label for="search">
                        Search
                    </label>

                    <input
                        id="search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Voucher, party, source..."
                    >
                </div>

                <div class="gl-buttons">
                    <button
                        type="button"
                        class="gl-button gl-button-secondary"
                        wire:click="resetFilters"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="gl-button gl-button-primary"
                        wire:click="$refresh"
                    >
                        Run Audit
                    </button>

                    <button
                        type="button"
                        class="gl-button gl-button-primary"
                        onclick="window.print()"
                    >
                        Print
                    </button>
                </div>
            </div>
        </section>

        <section class="gl-panel">
            <h2 class="gl-title">
                GL Integrity Summary
            </h2>

            <p class="gl-description">
                Period:
                {{ $report['period']['from'] }}
                to
                {{ $report['period']['to'] }}

                — Generated:
                {{ $report['generated_at'] }}
            </p>
        </section>

        <section class="gl-stats">
            <article class="gl-card">
                <div class="gl-card-label">
                    Vouchers Scanned
                </div>

                <div class="gl-card-value">
                    {{ number_format(
                        $stats['vouchers_scanned']
                    ) }}
                </div>
            </article>

            <article class="gl-card">
                <div class="gl-card-label">
                    Passed Vouchers
                </div>

                <div class="gl-card-value gl-success">
                    {{ number_format(
                        $stats['passed_vouchers']
                    ) }}
                </div>
            </article>

            <article class="gl-card gl-card-warning">
                <div class="gl-card-label">
                    Affected Vouchers
                </div>

                <div class="gl-card-value">
                    {{ number_format(
                        $stats['affected_vouchers']
                    ) }}
                </div>
            </article>

            <article class="gl-card gl-card-critical">
                <div class="gl-card-label">
                    Total Issues
                </div>

                <div class="gl-card-value gl-danger">
                    {{ number_format(
                        $stats['total_issues']
                    ) }}
                </div>
            </article>

            <article class="gl-card gl-card-critical">
                <div class="gl-card-label">
                    Critical Issues
                </div>

                <div class="gl-card-value gl-danger">
                    {{ number_format(
                        $stats['critical']
                    ) }}
                </div>
            </article>

            <article class="gl-card gl-card-warning">
                <div class="gl-card-label">
                    High Issues
                </div>

                <div class="gl-card-value">
                    {{ number_format(
                        $stats['high']
                    ) }}
                </div>
            </article>

            <article class="gl-card">
                <div class="gl-card-label">
                    Medium Issues
                </div>

                <div class="gl-card-value">
                    {{ number_format(
                        $stats['medium']
                    ) }}
                </div>
            </article>

            <article class="gl-card">
                <div class="gl-card-label">
                    Integrity Score
                </div>

                <div
                    class="gl-card-value {{
                        $stats['integrity_score'] >= 95
                            ? 'gl-success'
                            : 'gl-danger'
                    }}"
                >
                    {{ number_format(
                        $stats['integrity_score'],
                        1
                    ) }}
                    %
                </div>
            </article>

            <article class="gl-card gl-card-warning">
                <div class="gl-card-label">
                    Filtered Issues
                </div>

                <div class="gl-card-value">
                    {{ number_format(
                        $stats['filtered_issues']
                    ) }}
                </div>
            </article>

            <article class="gl-card gl-card-critical">
                <div class="gl-card-label">
                    Affected Voucher Value
                </div>

                <div class="gl-card-value">
                    PKR
                    {{ number_format(
                        $stats['affected_amount'],
                        2
                    ) }}
                </div>
            </article>
        </section>

        <section class="gl-panel">
            <h2 class="gl-title">
                Audit Findings
            </h2>

            <div class="gl-table-wrap">
                <table class="gl-table">
                    <thead>
                        <tr>
                            <th>Severity</th>
                            <th>Issue</th>
                            <th>Voucher</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Party</th>
                            <th>Source</th>
                            <th>Source No</th>
                            <th>Amount</th>
                            <th>Details</th>
                            <th>Recommended Action</th>
                            <th>Review</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (
                            $report['issue_rows']
                            as $row
                        )
                            <tr>
                                <td class="gl-nowrap">
                                    <span
                                        class="gl-badge gl-{{ $row['severity_key'] }}"
                                    >
                                        {{ $row['severity'] }}
                                    </span>
                                </td>

                                <td class="gl-nowrap">
                                    <strong>
                                        {{ $row['issue_label'] }}
                                    </strong>
                                </td>

                                <td class="gl-nowrap">
                                    {{ $row['voucher_no'] }}
                                </td>

                                <td class="gl-nowrap">
                                    {{ $row['voucher_date'] }}
                                </td>

                                <td class="gl-nowrap">
                                    {{ $row['voucher_type'] }}
                                </td>

                                <td class="gl-nowrap">
                                    <span
                                        class="gl-badge gl-status"
                                    >
                                        {{ $row['voucher_status'] }}
                                    </span>
                                </td>

                                <td class="gl-nowrap">
                                    {{ $row['party_name'] }}
                                </td>

                                <td class="gl-nowrap">
                                    {{ $row['source'] }}
                                </td>

                                <td class="gl-nowrap">
                                    {{ $row['source_no'] }}
                                </td>

                                <td class="gl-money">
                                    PKR
                                    {{ number_format(
                                        $row['amount'],
                                        2
                                    ) }}
                                </td>

                                <td class="gl-text-cell">
                                    {{ $row['details'] }}
                                </td>

                                <td class="gl-text-cell">
                                    {{ $row['recommendation'] }}
                                </td>

                                <td class="gl-nowrap">
                                    <a
                                        href="{{ $row['voucher_url'] }}"
                                        class="gl-open"
                                    >
                                        Open Voucher
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="13"
                                    class="gl-empty"
                                >
                                    Selected filters ke liye koi
                                    GL integrity issue nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>