@if ($canView)
    @php
        $passportReportUrl = route(
            'filament.admin.pages.passport-validity-report',
        );

        $passportService = app(
            \App\Services\PassportDashboardService::class,
        );
    @endphp

    <section class="kes-passport-dashboard">
        <style>
            .kes-passport-dashboard {
                margin-top: 24px;
                padding: 22px;
                border: 1px solid rgba(148, 163, 184, .24);
                border-radius: 18px;
                background:
                    linear-gradient(
                        135deg,
                        rgba(5, 87, 73, .08),
                        rgba(217, 164, 65, .08)
                    );
            }

            .kes-passport-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 18px;
            }

            .kes-passport-head h2 {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 800;
            }

            .kes-passport-head p {
                margin: 4px 0 0;
                color: #64748b;
                font-size: .88rem;
            }

            .kes-passport-report-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 9px 14px;
                border-radius: 10px;
                background: #056149;
                color: white;
                font-size: .84rem;
                font-weight: 700;
                text-decoration: none;
            }

            .kes-passport-stats {
                display: grid;
                grid-template-columns:
                    repeat(5, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 18px;
            }

            .kes-passport-stat {
                padding: 14px;
                border: 1px solid rgba(148, 163, 184, .22);
                border-radius: 14px;
                background: rgba(255, 255, 255, .82);
            }

            .dark .kes-passport-stat {
                background: rgba(15, 23, 42, .62);
            }

            .kes-passport-stat small {
                display: block;
                color: #64748b;
                font-weight: 700;
            }

            .kes-passport-stat strong {
                display: block;
                margin-top: 6px;
                font-size: 1.55rem;
            }

            .kes-passport-table-wrap {
                overflow-x: auto;
                border: 1px solid rgba(148, 163, 184, .22);
                border-radius: 14px;
                background: rgba(255, 255, 255, .82);
            }

            .dark .kes-passport-table-wrap {
                background: rgba(15, 23, 42, .62);
            }

            .kes-passport-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 880px;
            }

            .kes-passport-table th,
            .kes-passport-table td {
                padding: 12px;
                border-bottom:
                    1px solid rgba(148, 163, 184, .18);
                text-align: left;
                font-size: .84rem;
            }

            .kes-passport-table th {
                color: #64748b;
                font-weight: 800;
            }

            .kes-passport-badge {
                display: inline-flex;
                padding: 5px 9px;
                border-radius: 999px;
                font-size: .74rem;
                font-weight: 800;
            }

            .kes-passport-danger {
                background: #fee2e2;
                color: #991b1b;
            }

            .kes-passport-warning {
                background: #fef3c7;
                color: #92400e;
            }

            .kes-passport-success {
                background: #d1fae5;
                color: #065f46;
            }

            .kes-passport-link {
                color: #047857;
                font-weight: 800;
                text-decoration: none;
            }

            @media (max-width: 1100px) {
                .kes-passport-stats {
                    grid-template-columns:
                        repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .kes-passport-head {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .kes-passport-stats {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="kes-passport-head">
            <div>
                <h2>Passport Validity Control</h2>
                <p>
                    Six-month validity, expiry and journey
                    readiness monitoring.
                </p>
            </div>

            <a
                href="{{ $passportReportUrl }}"
                class="kes-passport-report-btn"
            >
                Open Full Report
            </a>
        </div>

        <div class="kes-passport-stats">
            <div class="kes-passport-stat">
                <small>Action Required</small>
                <strong>
                    {{ number_format(
                        $stats['action_required'] ?? 0
                    ) }}
                </strong>
            </div>

            <div class="kes-passport-stat">
                <small>Expired</small>
                <strong>
                    {{ number_format(
                        $stats['expired'] ?? 0
                    ) }}
                </strong>
            </div>

            <div class="kes-passport-stat">
                <small>Renewal Required</small>
                <strong>
                    {{ number_format(
                        $stats['renewal_required'] ?? 0
                    ) }}
                </strong>
            </div>

            <div class="kes-passport-stat">
                <small>Journey Invalid</small>
                <strong>
                    {{ number_format(
                        $stats['journey_invalid'] ?? 0
                    ) }}
                </strong>
            </div>

            <div class="kes-passport-stat">
                <small>Expiring in 180 Days</small>
                <strong>
                    {{ number_format(
                        $stats['expiring_soon'] ?? 0
                    ) }}
                </strong>
            </div>
        </div>

        <div class="kes-passport-table-wrap">
            <table class="kes-passport-table">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Customer</th>
                        <th>Passport</th>
                        <th>Expiry</th>
                        <th>Travel</th>
                        <th>Validity Status</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $displayStatus =
                                $passportService
                                    ->rowAlertStatus($row);

                            $badgeClass = match (
                                $displayStatus
                            ) {
                                'Expired',
                                'Not Valid for Journey',
                                'Missing Passport Expiry' =>
                                    'kes-passport-danger',

                                'Renewal Required',
                                'Passport Expiring Soon',
                                'Missing Travel Date' =>
                                    'kes-passport-warning',

                                default =>
                                    'kes-passport-success',
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
                                <span
                                    class="kes-passport-badge {{ $badgeClass }}"
                                >
                                    {{ $displayStatus }}
                                </span>
                            </td>

                            <td>
                                <a
                                    class="kes-passport-link"
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
                            <td colspan="7">
                                No passport alerts currently require
                                attention.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
