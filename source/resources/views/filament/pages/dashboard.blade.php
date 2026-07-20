<x-filament-panels::page>
    <div class="kes-admin-dashboard">
        <div class="kes-dashboard-title-row">
            <div>
                <h1>Dashboard</h1>
                <p>Welcome to Karwan E Al Saeed Travel & Tours</p>
            </div>
        </div>

        <div class="kes-welcome-card">
            <div class="kes-welcome-left">
                <div class="kes-avatar">
                    {{ $currentUserInitial }}
                </div>

                <div>
                    <h2>
                        Welcome back, {{ $currentUserName }}! 👋
                    </h2>

                    <p>
                        {{ $currentUserRole }} dashboard ·
                        Here’s what’s happening today.
                    </p>
                </div>
            </div>

            <div class="kes-welcome-meta">
                <div>
                    <span>📅</span>
                    <strong>{{ $currentDate }}</strong>
                </div>

                <div>
                    <span>🕒</span>
                    <strong>{{ $currentTime }}</strong>
                </div>
            </div>

            <div class="kes-welcome-art">
                <div class="kes-kaaba">🕋</div>
                <div class="kes-plane">✈</div>
            </div>
        </div>

        @if (count($stats) > 0)
            <div class="kes-stats-grid">
                @foreach ($stats as $stat)
                    <a
                        href="{{ $stat['url'] }}"
                        class="kes-stat-card kes-tone-{{ $stat['tone'] }}"
                    >
                        <div class="kes-stat-top">
                            <div class="kes-stat-icon">
                                {{ $stat['icon'] }}
                            </div>

                            <div class="kes-stat-menu">⋮</div>
                        </div>

                        <div class="kes-stat-label">
                            {{ $stat['label'] }}
                        </div>

                        <div class="kes-stat-value">
                            {{ $stat['value'] }}
                        </div>

                        <div class="kes-stat-footer">
                            <span>{{ $stat['trend'] }}</span>

                            <small>
                                {{ $stat['description'] }} →
                            </small>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="kes-dashboard-panels">
            @if ($showExecutiveFinance)
                <div class="kes-panel kes-chart-panel">
                    <div class="kes-panel-header">
                        <div>
                            <h3>Revenue Overview</h3>
                            <p>Actual received payments</p>
                        </div>

                        <span class="kes-chip">
                            Last 7 Days
                        </span>
                    </div>

                    <div class="kes-chart-amount">
                        {{ $totalRevenue }}
                    </div>

                    <div class="kes-chart-growth">
                        {{ $revenueTrend }}
                    </div>

                    @if ($chart['has_data'])
                        <svg
                            class="kes-chart"
                            viewBox="0 0 {{ $chart['width'] }} {{ $chart['height'] }}"
                            preserveAspectRatio="none"
                        >
                            <defs>
                                <linearGradient
                                    id="kesChartGradient"
                                    x1="0"
                                    x2="0"
                                    y1="0"
                                    y2="1"
                                >
                                    <stop
                                        offset="0%"
                                        stop-color="#0b4f34"
                                        stop-opacity="0.30"
                                    />

                                    <stop
                                        offset="100%"
                                        stop-color="#0b4f34"
                                        stop-opacity="0.02"
                                    />
                                </linearGradient>
                            </defs>

                            <path
                                d="{{ $chart['area'] }}"
                                fill="url(#kesChartGradient)"
                            />

                            <polyline
                                points="{{ $chart['polyline'] }}"
                                fill="none"
                                stroke="#0b4f34"
                                stroke-width="4"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            @foreach ($chart['points'] as $point)
                                <circle
                                    cx="{{ $point['x'] }}"
                                    cy="{{ $point['y'] }}"
                                    r="5"
                                    fill="#ffffff"
                                    stroke="#0b4f34"
                                    stroke-width="3"
                                />
                            @endforeach
                        </svg>
                    @else
                        <div
                            style="
                                min-height: 190px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                text-align: center;
                                color: #64748b;
                                font-weight: 600;
                            "
                        >
                            No payment collection recorded
                            during the last 7 days.
                        </div>
                    @endif

                    <div class="kes-chart-labels">
                        @foreach ($chart['labels'] as $label)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($showRecentBookings)
                <div class="kes-panel">
                    <div class="kes-panel-header">
                        <div>
                            <h3>Recent Bookings</h3>
                            <p>
                                Only bookings you are permitted
                                to view
                            </p>
                        </div>
                    </div>

                    <div class="kes-table-wrap">
                        <table class="kes-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Type</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($recentBookings as $booking)
                                    <tr>
                                        <td>{{ $booking['id'] }}</td>

                                        <td>
                                            <span class="kes-type-icon">
                                                {{ $booking['icon'] }}
                                            </span>

                                            {{ $booking['type'] }}
                                        </td>

                                        <td>
                                            {{ $booking['customer'] }}
                                        </td>

                                        <td>
                                            {{ $booking['amount'] }}
                                        </td>

                                        <td>
                                            <span class="kes-status">
                                                {{ $booking['status'] }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $booking['date'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            No recent bookings found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($showRecentPayments)
                <div class="kes-panel">
                    <div class="kes-panel-header">
                        <div>
                            <h3>Recent Payments</h3>
                            <p>
                                Latest authorized payment records
                            </p>
                        </div>

                        <a
                            href="/admin/daily-finance-report"
                            class="kes-small-btn"
                        >
                            Finance Report
                        </a>
                    </div>

                    <div class="kes-table-wrap">
                        <table class="kes-table">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Service</th>
                                    <th>Customer</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($recentPayments as $payment)
                                    <tr>
                                        <td>{{ $payment['id'] }}</td>

                                        <td>
                                            {{ $payment['service'] }}
                                        </td>

                                        <td>
                                            {{ $payment['customer'] }}
                                        </td>

                                        <td>
                                            {{ $payment['method'] }}
                                        </td>

                                        <td>
                                            {{ $payment['amount'] }}
                                        </td>

                                        <td>
                                            {{ $payment['date'] }}
                                        </td>

                                        <td>
                                            <span class="kes-status kes-status-success">
                                                {{ $payment['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            No recent payments found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <x-admin-task-dashboard />
        <x-admin-passport-dashboard />

        <div class="kes-dashboard-footer">
            <span>
                © {{ date('Y') }}
                Karwan E Al Saeed Travel & Tours.
                All rights reserved.
            </span>

            <span>
                v5.6.8 | Made with ❤️ in Pakistan
            </span>
        </div>
    </div>
</x-filament-panels::page>