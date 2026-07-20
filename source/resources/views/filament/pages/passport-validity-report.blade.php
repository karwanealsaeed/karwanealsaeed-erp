<x-filament-panels::page>
    <style>
        .kes-passport-report {
            display: grid;
            gap: 18px;
        }

        .kes-passport-report-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .kes-passport-report-head h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
        }

        .kes-passport-report-head p {
            margin: 5px 0 0;
            color: #64748b;
        }

        .kes-report-print {
            padding: 10px 15px;
            border: 0;
            border-radius: 10px;
            background: #056149;
            color: white;
            font-weight: 800;
            cursor: pointer;
        }

        .kes-report-filters {
            display: grid;
            grid-template-columns:
                1.4fr 1fr 1fr 1fr;
            gap: 12px;
            padding: 16px;
            border:
                1px solid rgba(148, 163, 184, .25);
            border-radius: 14px;
        }

        .kes-report-field label {
            display: block;
            margin-bottom: 6px;
            color: #64748b;
            font-size: .78rem;
            font-weight: 800;
        }

        .kes-report-field input,
        .kes-report-field select {
            width: 100%;
            padding: 9px 11px;
            border:
                1px solid rgba(148, 163, 184, .4);
            border-radius: 9px;
            background: transparent;
        }

        .kes-report-stats {
            display: grid;
            grid-template-columns:
                repeat(6, minmax(0, 1fr));
            gap: 10px;
        }

        .kes-report-stat {
            padding: 14px;
            border:
                1px solid rgba(148, 163, 184, .22);
            border-radius: 13px;
        }

        .kes-report-stat small {
            display: block;
            color: #64748b;
            font-weight: 700;
        }

        .kes-report-stat strong {
            display: block;
            margin-top: 6px;
            font-size: 1.35rem;
        }

        .kes-report-table-wrap {
            overflow-x: auto;
            border:
                1px solid rgba(148, 163, 184, .22);
            border-radius: 14px;
        }

        .kes-report-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        .kes-report-table th,
        .kes-report-table td {
            padding: 11px;
            border-bottom:
                1px solid rgba(148, 163, 184, .18);
            text-align: left;
            font-size: .82rem;
        }

        .kes-report-table th {
            color: #64748b;
            font-weight: 800;
        }

        .kes-report-status {
            display: inline-flex;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 800;
        }

        .kes-report-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .kes-report-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .kes-report-success {
            background: #d1fae5;
            color: #065f46;
        }

        .kes-report-link {
            color: #047857;
            font-weight: 800;
            text-decoration: none;
        }

        @media (max-width: 1100px) {
            .kes-report-filters {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .kes-report-stats {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .kes-passport-report-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .kes-report-filters,
            .kes-report-stats {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .kes-report-no-print {
                display: none !important;
            }

            .fi-main {
                padding: 0 !important;
            }

            .kes-passport-report {
                color: #111827 !important;
            }

            .kes-report-table {
                min-width: 0;
            }

            .kes-report-table th,
            .kes-report-table td {
                padding: 6px;
                font-size: 10px;
            }
        }
    </style>

    <div class="kes-passport-report">
        <div class="kes-passport-report-head">
            <div>
                <h1>Passport Validity Report</h1>

                <p>
                    Generated:
                    {{ now()->format(
                        'd M Y h:i A'
                    ) }}
                </p>
            </div>

            <button
                type="button"
                class="kes-report-print kes-report-no-print"
                onclick="window.print()"
            >
                Print / Save PDF
            </button>
        </div>

        <div class="kes-report-filters kes-report-no-print">
            <div class="kes-report-field">
                <label>Search</label>

                <input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Application, customer or passport"
                >
            </div>

            <div class="kes-report-field">
                <label>Validity Status</label>

                <select
                    wire:model.live="statusFilter"
                >
                    @foreach (
                        $statusOptions
                        as $value => $label
                    )
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="kes-report-field">
                <label>Passport Expiry From</label>

                <input
                    type="date"
                    wire:model.live="fromDate"
                >
            </div>

            <div class="kes-report-field">
                <label>Passport Expiry To</label>

                <input
                    type="date"
                    wire:model.live="toDate"
                >
            </div>
        </div>

        <div class="kes-report-stats">
            <div class="kes-report-stat">
                <small>Total Active</small>
                <strong>
                    {{ number_format(
                        $stats['total']
                    ) }}
                </strong>
            </div>

            <div class="kes-report-stat">
                <small>Action Required</small>
                <strong>
                    {{ number_format(
                        $stats['action_required']
                    ) }}
                </strong>
            </div>

            <div class="kes-report-stat">
                <small>Expired</small>
                <strong>
                    {{ number_format(
                        $stats['expired']
                    ) }}
                </strong>
            </div>

            <div class="kes-report-stat">
                <small>Renewal Required</small>
                <strong>
                    {{ number_format(
                        $stats['renewal_required']
                    ) }}
                </strong>
            </div>

            <div class="kes-report-stat">
                <small>Journey Invalid</small>
                <strong>
                    {{ number_format(
                        $stats['journey_invalid']
                    ) }}
                </strong>
            </div>

            <div class="kes-report-stat">
                <small>Expiring Soon</small>
                <strong>
                    {{ number_format(
                        $stats['expiring_soon']
                    ) }}
                </strong>
            </div>
        </div>

        <div>
            <strong>
                {{ number_format(
                    $rows->count()
                ) }}
                records shown
            </strong>
        </div>

        <div class="kes-report-table-wrap">
            <table class="kes-report-table">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Customer</th>
                        <th>Country</th>
                        <th>Passport</th>
                        <th>Expiry</th>
                        <th>Travel</th>
                        <th>Return</th>
                        <th>Required Until</th>
                        <th>Validity</th>
                        <th>Application Status</th>
                        <th class="kes-report-no-print"></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $displayStatus =
                                $passportService
                                    ->rowAlertStatus($row);

                            $statusClass = match (
                                $displayStatus
                            ) {
                                'Expired',
                                'Not Valid for Journey',
                                'Missing Passport Expiry' =>
                                    'kes-report-danger',

                                'Renewal Required',
                                'Passport Expiring Soon',
                                'Missing Travel Date' =>
                                    'kes-report-warning',

                                default =>
                                    'kes-report-success',
                            };
                        @endphp

                        <tr>
                            <td>
                                {{ $row->application_no
                                    ?: '#'.$row->id }}
                            </td>

                            <td>
                                {{ $row->customer?->name
                                    ?: '-' }}
                            </td>

                            <td>
                                {{ $row->country?->name
                                    ?: '-' }}
                            </td>

                            <td>
                                {{ $row->passport_no
                                    ?: '-' }}
                            </td>

                            <td>
                                {{ $row->passport_expiry_date
                                    ?->format('d M Y')
                                    ?: '-' }}
                            </td>

                            <td>
                                {{ $row->travel_date
                                    ?->format('d M Y')
                                    ?: '-' }}
                            </td>

                            <td>
                                {{ $row->return_date
                                    ?->format('d M Y')
                                    ?: '-' }}
                            </td>

                            <td>
                                {{ $row->passport_required_until
                                    ?->format('d M Y')
                                    ?: '-' }}
                            </td>

                            <td>
                                <span
                                    class="kes-report-status {{ $statusClass }}"
                                >
                                    {{ $displayStatus }}
                                </span>
                            </td>

                            <td>
                                {{ $row->status ?: '-' }}
                            </td>

                            <td class="kes-report-no-print">
                                <a
                                    class="kes-report-link"
                                    href="{{ route(
                                        'filament.admin.resources.'
                                        .'visa-applications.view',
                                        [
                                            'record' => $row,
                                        ],
                                    ) }}"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                No Passport records match the
                                selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
