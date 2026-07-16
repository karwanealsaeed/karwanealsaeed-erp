<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>
        Customer Statement -
        {{ $report['customer']['name'] ?? 'Customer' }}
    </title>

    <style>
        @page {
            margin: 14mm 10mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: #111827;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8px;
            line-height: 1.35;
        }

        .header-table,
        .information-table,
        .summary-table,
        .statement-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table {
            margin-bottom: 10px;
            border-bottom: 2px solid #0f766e;
        }

        .header-table td {
            padding: 0 0 8px;
            vertical-align: top;
        }

        .company-name {
            margin: 0;
            color: #0f766e;
            font-size: 18px;
            font-weight: bold;
        }

        .company-brand {
            margin-top: 2px;
            color: #111827;
            font-size: 11px;
            font-weight: bold;
        }

        .company-details {
            margin-top: 4px;
            color: #4b5563;
            font-size: 7.5px;
            line-height: 1.5;
        }

        .document-heading {
            text-align: right;
        }

        .document-title {
            margin: 0;
            color: #111827;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .document-period {
            margin-top: 4px;
            color: #4b5563;
            font-size: 8px;
        }

        .section-heading {
            margin: 10px 0 5px;
            padding: 5px 7px;
            background: #0f766e;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .information-table {
            margin-bottom: 8px;
            border: 1px solid #d1d5db;
        }

        .information-table td {
            width: 25%;
            padding: 5px 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        .field-label {
            color: #6b7280;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .field-value {
            margin-top: 2px;
            color: #111827;
            font-size: 8px;
            font-weight: bold;
        }

        .summary-table {
            margin-bottom: 9px;
            table-layout: fixed;
        }

        .summary-table td {
            padding: 6px;
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: middle;
        }

        .summary-label {
            color: #6b7280;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 3px;
            color: #111827;
            font-size: 10px;
            font-weight: bold;
        }

        .receivable-box {
            background: #fef2f2;
        }

        .advance-box {
            background: #f0fdf4;
        }

        .closing-box {
            background: #f0fdfa;
        }

        .statement-table {
            table-layout: fixed;
        }

        .statement-table thead {
            display: table-header-group;
        }

        .statement-table tr {
            page-break-inside: avoid;
        }

        .statement-table th {
            padding: 5px 4px;
            border: 1px solid #9ca3af;
            background: #e5e7eb;
            color: #111827;
            font-size: 6.4px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
        }

        .statement-table td {
            padding: 4px;
            border: 1px solid #d1d5db;
            vertical-align: top;
            word-wrap: break-word;
        }

        .opening-row td {
            background: #f3f4f6;
            font-weight: bold;
        }

        .total-row td {
            background: #ecfdf5;
            font-weight: bold;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .nowrap {
            white-space: nowrap;
        }

        .description {
            line-height: 1.3;
        }

        .transaction-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 6px;
            font-weight: bold;
        }

        .transaction-invoice {
            background: #dbeafe;
            color: #1e3a8a;
        }

        .transaction-receipt {
            background: #dcfce7;
            color: #14532d;
        }

        .transaction-refund {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .balance-receivable {
            color: #991b1b;
            font-weight: bold;
        }

        .balance-advance {
            color: #166534;
            font-weight: bold;
        }

        .balance-settled {
            color: #374151;
            font-weight: bold;
        }

        .empty-row {
            padding: 18px !important;
            text-align: center;
            color: #6b7280;
        }

        .notes {
            margin-top: 9px;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            color: #4b5563;
            font-size: 6.5px;
            line-height: 1.5;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -10mm;
            left: 0;
            padding-top: 4px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 6px;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        $customer = $report['customer'];
        $stats = $report['stats'];

        $companyName =
            $company['name']
            ?? config('app.name');

        $companyBrand =
            $company['brand']
            ?? '';

        $companyPhone =
            $company['phone']
            ?? '';

        $companyEmail =
            $company['email']
            ?? '';

        $companyAddress =
            $company['address']
            ?? '';
    @endphp

    <div class="footer">
        Confidential customer statement generated by
        {{ $companyBrand ?: $companyName }}
        on {{ $report['generated_at'] }}.
    </div>

    <table class="header-table">
        <tr>
            <td style="width: 62%;">
                <h1 class="company-name">
                    {{ $companyName }}
                </h1>

                @if (filled($companyBrand))
                    <div class="company-brand">
                        {{ $companyBrand }}
                    </div>
                @endif

                <div class="company-details">
                    @if (filled($companyAddress))
                        {{ $companyAddress }}
                    @endif

                    @if (
                        filled($companyAddress)
                        && (
                            filled($companyPhone)
                            || filled($companyEmail)
                        )
                    )
                        <br>
                    @endif

                    @if (filled($companyPhone))
                        Phone: {{ $companyPhone }}
                    @endif

                    @if (
                        filled($companyPhone)
                        && filled($companyEmail)
                    )
                        &nbsp; | &nbsp;
                    @endif

                    @if (filled($companyEmail))
                        Email: {{ $companyEmail }}
                    @endif
                </div>
            </td>

            <td
                style="width: 38%;"
                class="document-heading"
            >
                <h2 class="document-title">
                    Customer Statement
                </h2>

                <div class="document-period">
                    Period:
                    {{ $report['period']['from'] }}
                    to
                    {{ $report['period']['to'] }}
                </div>

                <div class="document-period">
                    Generated:
                    {{ $report['generated_at'] }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-heading">
        Customer / Agent Information
    </div>

    <table class="information-table">
        <tr>
            <td>
                <div class="field-label">
                    Customer / Agent Name
                </div>

                <div class="field-value">
                    {{ $customer['name'] }}
                </div>
            </td>

            <td>
                <div class="field-label">
                    Customer Type
                </div>

                <div class="field-value">
                    {{ $customer['customer_type'] }}
                </div>
            </td>

            <td>
                <div class="field-label">
                    Mobile
                </div>

                <div class="field-value">
                    {{ $customer['mobile'] }}
                </div>
            </td>

            <td>
                <div class="field-label">
                    WhatsApp
                </div>

                <div class="field-value">
                    {{ $customer['whatsapp'] }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="field-label">
                    Email
                </div>

                <div class="field-value">
                    {{ $customer['email'] }}
                </div>
            </td>

            <td>
                <div class="field-label">
                    CNIC
                </div>

                <div class="field-value">
                    {{ $customer['cnic'] }}
                </div>
            </td>

            <td>
                <div class="field-label">
                    Passport No
                </div>

                <div class="field-value">
                    {{ $customer['passport_no'] }}
                </div>
            </td>

            <td>
                <div class="field-label">
                    Customer Status
                </div>

                <div class="field-value">
                    {{ $customer['status'] }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-heading">
        Statement Summary
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">
                    Opening Balance
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['opening_balance'],
                        2
                    ) }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Period Debit
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['period_debit'],
                        2
                    ) }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Period Credit
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['period_credit'],
                        2
                    ) }}
                </div>
            </td>

            <td class="closing-box">
                <div class="summary-label">
                    Closing Balance
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['closing_balance'],
                        2
                    ) }}
                </div>

                <div style="margin-top: 2px;">
                    {{ $report['closing_status'] }}
                </div>
            </td>

            <td class="receivable-box">
                <div class="summary-label">
                    Amount Receivable
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['amount_receivable'],
                        2
                    ) }}
                </div>
            </td>

            <td class="advance-box">
                <div class="summary-label">
                    Advance Available
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['advance_available'],
                        2
                    ) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-heading">
        Customer Ledger Transactions
    </div>

    <table class="statement-table">
        <thead>
            <tr>
                <th style="width: 7%;">
                    Date
                </th>

                <th style="width: 9%;">
                    Transaction
                </th>

                <th style="width: 9%;">
                    Reference
                </th>

                <th style="width: 7%;">
                    Service
                </th>

                <th style="width: 20%;">
                    Description
                </th>

                <th style="width: 8%;">
                    Method
                </th>

                <th
                    style="width: 9%;"
                    class="text-right"
                >
                    Debit
                </th>

                <th
                    style="width: 9%;"
                    class="text-right"
                >
                    Credit
                </th>

                <th
                    style="width: 11%;"
                    class="text-right"
                >
                    Running Balance
                </th>

                <th style="width: 11%;">
                    Balance Nature
                </th>
            </tr>
        </thead>

        <tbody>
            <tr class="opening-row">
                <td colspan="8">
                    Opening balance before
                    {{ $report['period']['from'] }}
                </td>

                <td class="text-right nowrap">
                    PKR
                    {{ number_format(
                        $stats['opening_balance'],
                        2
                    ) }}
                </td>

                <td>
                    Opening
                </td>
            </tr>

            @forelse ($report['rows'] as $row)
                @php
                    $transactionClass =
                        $row['transaction_key']
                        === 'invoice'
                            ? 'transaction-invoice'
                            : (
                                str_contains(
                                    $row['transaction_key'],
                                    'refund'
                                )
                                    ? 'transaction-refund'
                                    : 'transaction-receipt'
                            );

                    $balanceClass =
                        match (
                            $row['balance_key']
                        ) {
                            'receivable' =>
                                'balance-receivable',

                            'advance' =>
                                'balance-advance',

                            default =>
                                'balance-settled',
                        };
                @endphp

                <tr>
                    <td class="nowrap">
                        {{ $row['date'] }}
                    </td>

                    <td>
                        <span
                            class="transaction-badge {{ $transactionClass }}"
                        >
                            {{ $row['transaction_type'] }}
                        </span>
                    </td>

                    <td>
                        {{ $row['source_no'] }}
                    </td>

                    <td>
                        {{ $row['service_type'] }}
                    </td>

                    <td class="description">
                        {{ $row['description'] }}

                        @if (
                            filled($row['reference_no'])
                            && $row['reference_no'] !== '-'
                        )
                            <br>

                            <span style="color: #6b7280;">
                                External Ref:
                                {{ $row['reference_no'] }}
                            </span>
                        @endif
                    </td>

                    <td>
                        {{ $row['payment_method'] }}
                    </td>

                    <td class="text-right nowrap">
                        @if ($row['debit'] > 0)
                            PKR
                            {{ number_format(
                                $row['debit'],
                                2
                            ) }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="text-right nowrap">
                        @if ($row['credit'] > 0)
                            PKR
                            {{ number_format(
                                $row['credit'],
                                2
                            ) }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="text-right nowrap">
                        PKR
                        {{ number_format(
                            $row['running_balance'],
                            2
                        ) }}
                    </td>

                    <td class="{{ $balanceClass }}">
                        {{ $row['balance_label'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="10"
                        class="empty-row"
                    >
                        Selected period ke liye koi
                        customer transaction nahi mila.
                    </td>
                </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="6">
                    Statement Period Totals
                </td>

                <td class="text-right nowrap">
                    PKR
                    {{ number_format(
                        $stats['period_debit'],
                        2
                    ) }}
                </td>

                <td class="text-right nowrap">
                    PKR
                    {{ number_format(
                        $stats['period_credit'],
                        2
                    ) }}
                </td>

                <td class="text-right nowrap">
                    PKR
                    {{ number_format(
                        $stats['closing_balance'],
                        2
                    ) }}
                </td>

                <td>
                    {{ $report['closing_status'] }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="notes">
        <strong>Statement Note:</strong>

        Positive closing balance ka matlab customer se
        amount receivable hai. Negative closing balance
        customer advance ko represent karta hai. Kisi
        discrepancy ki surat mein company accounts
        department se rabta karein.
    </div>
</body>
</html>