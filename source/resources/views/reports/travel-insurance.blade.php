<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Travel Insurance Report</title>

    <style>
        @page {
            margin: 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
        }

        h1 {
            margin: 0 0 4px;
            color: #14532d;
            font-size: 20px;
        }

        .muted {
            color: #6b7280;
        }

        .toolbar {
            margin-bottom: 15px;
        }

        .toolbar button {
            padding: 8px 14px;
            color: #ffffff;
            background: #14532d;
            border: 0;
            border-radius: 5px;
            cursor: pointer;
        }

        .summary {
            width: 100%;
            margin: 14px 0;
            border-collapse: separate;
            border-spacing: 6px;
        }

        .summary td {
            width: 20%;
            padding: 8px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 5px;
        }

        .summary span {
            display: block;
            color: #6b7280;
            font-size: 8px;
        }

        .summary strong {
            display: block;
            margin-top: 3px;
            font-size: 12px;
        }

        .filters {
            margin-bottom: 12px;
            padding: 7px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th,
        table.report td {
            padding: 5px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        table.report th {
            color: #ffffff;
            background: #14532d;
            font-size: 8px;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }

        .warning {
            margin-bottom: 10px;
            padding: 7px;
            color: #92400e;
            background: #fffbeb;
            border: 1px solid #fde68a;
        }

        @media print {
            .toolbar {
                display: none;
            }
        }
    </style>
</head>

<body>
    @unless ($isPdf)
        <div class="toolbar">
            <button
                type="button"
                onclick="window.print()"
            >
                Print Report
            </button>
        </div>
    @endunless

    <header>
        <h1>Karwan E Al Saeed Travel & Tours</h1>

        <strong>Travel Insurance Report</strong>

        <div class="muted">
            Generated {{ now()->format('d M Y h:i A') }}
        </div>
    </header>

    <div class="filters">
        Date:
        {{ $filters['date_from'] ?: 'Any' }}
        to
        {{ $filters['date_to'] ?: 'Any' }}

        |
        Status: {{ $filters['status'] ?: 'All' }}

        |
        Approval: {{ $filters['approval_status'] ?: 'All' }}

        |
        Payment: {{ $filters['payment_status'] ?: 'All' }}

        |
        Search: {{ $filters['search'] ?: 'None' }}
    </div>

    @if ($truncated)
        <div class="warning">
            This output was limited for safe rendering.
            Use the Excel CSV export for the complete dataset.
        </div>
    @endif

    <table class="summary">
        <tr>
            <td>
                <span>Policies</span>
                <strong>{{ number_format($summary['policy_count']) }}</strong>
            </td>

            <td>
                <span>Total Sales</span>
                <strong>PKR {{ number_format($summary['total_amount'], 2) }}</strong>
            </td>

            <td>
                <span>Received</span>
                <strong>PKR {{ number_format($summary['paid_amount'], 2) }}</strong>
            </td>

            <td>
                <span>Outstanding</span>
                <strong>PKR {{ number_format($summary['balance_amount'], 2) }}</strong>
            </td>

            <td>
                <span>Profit</span>
                <strong>PKR {{ number_format($summary['profit_amount'], 2) }}</strong>
            </td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>Policy</th>
                <th>Customer</th>
                <th>Traveller</th>
                <th>Provider</th>
                <th>Destination</th>
                <th>Validity</th>
                <th>Status</th>
                <th class="right">Total</th>
                <th class="right">Paid</th>
                <th class="right">Balance</th>
                <th class="right">Profit</th>
                <th>Invoice</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $policy)
                <tr>
                    <td class="nowrap">
                        {{ $policy->policy_no }}
                        <br>
                        {{ $policy->passport_no ?: '-' }}
                    </td>

                    <td>
                        {{ $policy->customer?->name ?: '-' }}
                    </td>

                    <td>
                        {{ $policy->traveller_name ?: '-' }}
                    </td>

                    <td>
                        {{ $policy->provider?->display_name ?: '-' }}
                    </td>

                    <td>
                        {{ $policy->destination_country ?: '-' }}
                    </td>

                    <td class="nowrap">
                        {{ $policy->policy_start_date?->format('d M Y') ?: '-' }}
                        <br>
                        {{ $policy->policy_end_date?->format('d M Y') ?: '-' }}
                    </td>

                    <td>
                        {{ $policy->status }}
                        <br>
                        {{ $policy->approval_status }}
                        /
                        {{ $policy->payment_status }}
                    </td>

                    <td class="right nowrap">
                        {{ number_format((float) $policy->total_amount, 2) }}
                    </td>

                    <td class="right nowrap">
                        {{ number_format((float) $policy->paid_amount, 2) }}
                    </td>

                    <td class="right nowrap">
                        {{ number_format((float) $policy->balance_amount, 2) }}
                    </td>

                    <td class="right nowrap">
                        {{ number_format((float) $policy->profit_amount, 2) }}
                    </td>

                    <td>
                        {{ $policy->invoice?->invoice_no ?: '-' }}
                        <br>
                        {{ $policy->invoice?->status ?: '' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="12"
                        style="padding: 20px; text-align: center;"
                    >
                        No records matched the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>