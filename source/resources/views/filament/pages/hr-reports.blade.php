<x-filament-panels::page>
    @php
        $departmentOptions = $this->departmentOptions();
        $staffOptions = $this->staffOptions();
        $report = $this->reportData();
        $stats = $report['stats'];
    @endphp

    <style>
        .hr-report-page {
            display: grid;
            gap: 1.25rem;
        }

        .hr-report-panel {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .dark .hr-report-panel {
            background: #111827;
            border-color: #374151;
        }

        .hr-report-heading {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
        }

        .dark .hr-report-heading {
            color: #f9fafb;
        }

        .hr-report-subheading {
            margin: .3rem 0 0;
            color: #6b7280;
            font-size: .875rem;
        }

        .dark .hr-report-subheading {
            color: #9ca3af;
        }

        .hr-filter-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            align-items: end;
        }

        .hr-field label {
            display: block;
            margin-bottom: .4rem;
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
        }

        .dark .hr-field label {
            color: #d1d5db;
        }

        .hr-field input,
        .hr-field select {
            width: 100%;
            min-height: 2.6rem;
            border: 1px solid #d1d5db;
            border-radius: .65rem;
            padding: .55rem .75rem;
            background: white;
            color: #111827;
            font-size: .875rem;
        }

        .dark .hr-field input,
        .dark .hr-field select {
            background: #1f2937;
            border-color: #4b5563;
            color: #f9fafb;
        }

        .hr-action-group {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .hr-button {
            min-height: 2.6rem;
            border: 0;
            border-radius: .65rem;
            padding: .55rem .9rem;
            cursor: pointer;
            font-weight: 600;
            font-size: .82rem;
        }

        .hr-button-primary {
            background: #0f766e;
            color: white;
        }

        .hr-button-secondary {
            background: #e5e7eb;
            color: #111827;
        }

        .dark .hr-button-secondary {
            background: #374151;
            color: #f9fafb;
        }

        .hr-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .hr-stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .dark .hr-stat-card {
            background: #111827;
            border-color: #374151;
        }

        .hr-stat-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #0f766e;
        }

        .hr-stat-label {
            color: #6b7280;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .dark .hr-stat-label {
            color: #9ca3af;
        }

        .hr-stat-value {
            margin-top: .45rem;
            color: #111827;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .dark .hr-stat-value {
            color: #f9fafb;
        }

        .hr-table-wrap {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .hr-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .hr-table th {
            padding: .75rem;
            text-align: left;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: .74rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .hr-table th {
            background: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .hr-table td {
            padding: .8rem .75rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: .82rem;
            white-space: nowrap;
        }

        .dark .hr-table td {
            border-color: #374151;
            color: #e5e7eb;
        }

        .hr-badge {
            display: inline-flex;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
        }

        .hr-badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .hr-badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .hr-badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .hr-badge-gray {
            background: #e5e7eb;
            color: #374151;
        }

        .hr-positive {
            color: #166534;
            font-weight: 700;
        }

        .hr-negative {
            color: #b91c1c;
            font-weight: 700;
        }

        .hr-empty {
            padding: 2rem;
            text-align: center;
            color: #6b7280;
        }

        @media (max-width: 1200px) {
            .hr-filter-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .hr-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .hr-filter-grid,
            .hr-stat-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .hr-report-filters,
            .hr-no-print {
                display: none !important;
            }

            .hr-report-panel,
            .hr-stat-card {
                box-shadow: none;
                break-inside: avoid;
            }

            .hr-report-page {
                display: block;
            }

            .hr-stat-grid {
                grid-template-columns: repeat(4, 1fr);
                margin-bottom: 1rem;
            }

            .hr-table {
                min-width: 100%;
                font-size: 10px;
            }
        }
    </style>

    <div class="hr-report-page">
        <section class="hr-report-panel hr-report-filters">
            <h2 class="hr-report-heading">
                Report Filters
            </h2>

            <p class="hr-report-subheading">
                Date range, department ya individual staff select karein.
            </p>

            <div class="hr-filter-grid">
                <div class="hr-field">
                    <label for="dateFrom">From Date</label>

                    <input
                        id="dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                    >
                </div>

                <div class="hr-field">
                    <label for="dateTo">To Date</label>

                    <input
                        id="dateTo"
                        type="date"
                        wire:model.live="dateTo"
                    >
                </div>

                <div class="hr-field">
                    <label for="departmentId">
                        Department
                    </label>

                    <select
                        id="departmentId"
                        wire:model.live="departmentId"
                    >
                        <option value="">
                            All Departments
                        </option>

                        @foreach ($departmentOptions as $id => $name)
                            <option value="{{ $id }}">
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="hr-field">
                    <label for="staffId">
                        Staff Member
                    </label>

                    <select
                        id="staffId"
                        wire:model.live="staffId"
                    >
                        <option value="">
                            All Staff Members
                        </option>

                        @foreach ($staffOptions as $id => $name)
                            <option value="{{ $id }}">
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="hr-action-group">
                    <button
                        type="button"
                        class="hr-button hr-button-secondary"
                        wire:click="resetFilters"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="hr-button hr-button-primary"
                        onclick="window.print()"
                    >
                        Print Report
                    </button>
                </div>
            </div>
        </section>

        <section class="hr-report-panel">
            <h2 class="hr-report-heading">
                HR Summary
            </h2>

            <p class="hr-report-subheading">
                Period:
                {{ $report['period']['from'] }}
                to
                {{ $report['period']['to'] }}
            </p>
        </section>

        <section class="hr-stat-grid">
            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Total Staff
                </div>

                <div class="hr-stat-value">
                    {{ number_format($stats['total_staff']) }}
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Active Staff
                </div>

                <div class="hr-stat-value">
                    {{ number_format($stats['active_staff']) }}
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    New Joiners
                </div>

                <div class="hr-stat-value">
                    {{ number_format($stats['new_joiners']) }}
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Pending Leaves
                </div>

                <div class="hr-stat-value">
                    {{ number_format($stats['pending_leaves']) }}
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Approved Leave Days
                </div>

                <div class="hr-stat-value">
                    {{ number_format(
                        $stats['approved_leave_days'],
                        2
                    ) }}
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Expiring Documents
                </div>

                <div class="hr-stat-value">
                    {{ number_format(
                        $stats['expiring_documents']
                    ) }}
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Final Reviews
                </div>

                <div class="hr-stat-value">
                    {{ number_format($stats['final_reviews']) }}
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Average Rating
                </div>

                <div class="hr-stat-value">
                    {{ number_format(
                        $stats['average_rating'],
                        1
                    ) }}
                    / 5
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Ledger Debit
                </div>

                <div class="hr-stat-value">
                    PKR
                    {{ number_format(
                        $stats['ledger_debit'],
                        2
                    ) }}
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Ledger Credit
                </div>

                <div class="hr-stat-value">
                    PKR
                    {{ number_format(
                        $stats['ledger_credit'],
                        2
                    ) }}
                </div>
            </article>

            <article class="hr-stat-card">
                <div class="hr-stat-label">
                    Ledger Balance
                </div>

                <div
                    class="hr-stat-value {{
                        $stats['ledger_balance'] >= 0
                            ? 'hr-negative'
                            : 'hr-positive'
                    }}"
                >
                    PKR
                    {{ number_format(
                        abs($stats['ledger_balance']),
                        2
                    ) }}

                    {{ $stats['ledger_balance'] >= 0
                        ? 'Recoverable'
                        : 'Payable'
                    }}
                </div>
            </article>
        </section>

        <section class="hr-report-panel">
            <h2 class="hr-report-heading">
                Department-wise Summary
            </h2>

            <p class="hr-report-subheading">
                Staff, leaves, documents, performance aur ledger summary.
            </p>

            <div class="hr-table-wrap">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Total Staff</th>
                            <th>Active</th>
                            <th>Pending Leaves</th>
                            <th>Approved Days</th>
                            <th>Expiring Docs</th>
                            <th>Rating</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (
                            $report['department_rows'] as $row
                        )
                            <tr>
                                <td>
                                    <strong>
                                        {{ $row['department'] }}
                                    </strong>
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['staff_count']
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['active_staff']
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['pending_leaves']
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['approved_leave_days'],
                                        2
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['expiring_documents']
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['average_rating'],
                                        1
                                    ) }}
                                    / 5
                                </td>

                                <td>
                                    PKR
                                    {{ number_format(
                                        $row['ledger_debit'],
                                        2
                                    ) }}
                                </td>

                                <td>
                                    PKR
                                    {{ number_format(
                                        $row['ledger_credit'],
                                        2
                                    ) }}
                                </td>

                                <td
                                    class="{{
                                        $row['ledger_balance'] >= 0
                                            ? 'hr-negative'
                                            : 'hr-positive'
                                    }}"
                                >
                                    PKR
                                    {{ number_format(
                                        abs(
                                            $row['ledger_balance']
                                        ),
                                        2
                                    ) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="10"
                                    class="hr-empty"
                                >
                                    No department data found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="hr-report-panel">
            <h2 class="hr-report-heading">
                Staff-wise Summary
            </h2>

            <p class="hr-report-subheading">
                Individual staff performance aur financial position.
            </p>

            <div class="hr-table-wrap">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Staff Name</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Joining</th>
                            <th>Status</th>
                            <th>Basic Salary</th>
                            <th>Pending Leave</th>
                            <th>Approved Days</th>
                            <th>Expiring Docs</th>
                            <th>Rating</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($report['staff_rows'] as $row)
                            <tr>
                                <td>
                                    <span class="hr-badge hr-badge-gray">
                                        {{ $row['staff_code'] }}
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        {{ $row['name'] }}
                                    </strong>
                                </td>

                                <td>{{ $row['department'] }}</td>
                                <td>{{ $row['designation'] }}</td>
                                <td>{{ $row['joining_date'] }}</td>

                                <td>
                                    <span
                                        class="hr-badge {{
                                            $row['status'] === 'Active'
                                                ? 'hr-badge-success'
                                                : 'hr-badge-warning'
                                        }}"
                                    >
                                        {{ $row['status'] }}
                                    </span>
                                </td>

                                <td>
                                    PKR
                                    {{ number_format(
                                        $row['basic_salary'],
                                        2
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['pending_leaves']
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['approved_leave_days'],
                                        2
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['expiring_documents']
                                    ) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $row['average_rating'],
                                        1
                                    ) }}
                                    / 5
                                </td>

                                <td>
                                    PKR
                                    {{ number_format(
                                        $row['ledger_debit'],
                                        2
                                    ) }}
                                </td>

                                <td>
                                    PKR
                                    {{ number_format(
                                        $row['ledger_credit'],
                                        2
                                    ) }}
                                </td>

                                <td
                                    class="{{
                                        $row['ledger_balance'] >= 0
                                            ? 'hr-negative'
                                            : 'hr-positive'
                                    }}"
                                >
                                    PKR
                                    {{ number_format(
                                        abs(
                                            $row['ledger_balance']
                                        ),
                                        2
                                    ) }}

                                    <small>
                                        {{ $row['ledger_balance'] >= 0
                                            ? 'Recoverable'
                                            : 'Payable'
                                        }}
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="14"
                                    class="hr-empty"
                                >
                                    Selected filters ke liye staff data nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>