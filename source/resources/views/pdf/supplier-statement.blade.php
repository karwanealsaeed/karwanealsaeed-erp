<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>
        Supplier Statement -
        {{ $report['supplier']['name'] ?? 'Supplier' }}
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

        .closing-box {
            background: #f0fdfa;
        }

        .payable-box {
            background: #fef2f2;
        }

        .advance-box {
            background: #f0fdf4;
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

        .transaction-bill {
            background: #dbeafe;
            color: #1e3a8a;
        }

        .transaction-payment {
            background: #dcfce7;
            color: #14532d;
        }

        .balance-payable {
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
        $supplier = $report['supplier'];
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
        Confidential supplier statement generated by
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
                    Supplier Statement
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
        Supplier Information
    </div>

    <table class="information-table">
        <tr>
            <td>
                <div class="field-label">
                    Supplier Name
                </div>

                <div class="field-value">
                    {{ $supplier['name'] }}
                </div>
            </td>

            <td>
                <div class="field-label">
                    Supplier Type
                </div>

                <div class="field-value">
                    {{ $supplier['supplier_type'] }}
                </div>
            </td>

            <td>
                <div class="field-label">
                    Phone
                </div>

                <div class="field-value">
                    {{ $supplier['phone'] }}
                </div>
            </td>

            <td>
                <div class="field-label">
                    Status
                </div>

                <div class="field-value">
                    {{ $supplier['status'] }}
                </div>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <div class="field-label">
                    Email
                </div>

                <div class="field-value">
                    {{ $supplier['email'] }}
                </div>
            </td>

            <td colspan="2">
                <div class="field-label">
                    Address
                </div>

                <div class="field-value">
                    {{ $supplier['address'] }}
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
                    Supplier Bills
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['period_credit'],
                        2
                    ) }}
                </div>
            </td>

            <td>
                <div class="summary-label">
                    Supplier Payments
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['period_debit'],
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

            <td class="payable-box">
                <div class="summary-label">
                    Amount Payable
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['amount_payable'],
                        2
                    ) }}
                </div>
            </td>

            <td class="advance-box">
                <div class="summary-label">
                    Supplier Advance
                </div>

                <div class="summary-value">
                    PKR
                    {{ number_format(
                        $stats['supplier_advance'],
                        2
                    ) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-heading">
        Supplier Ledger Transactions
    </div>

    <table class="statement-table">
        <thead>
            <tr>
                <th style="width: 7%;">
                    Date
                </th>

                <th style="width: 10%;">
                    Transaction
                </th>

                <th style="width: 9%;">
                    Reference
                </th>

                <th style="width: 7%;">
                    Service
                </th>

                <th style="width: 23%;">
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
                    style="width: 10%;"
                    class="text-right"
                >
                    Running Balance
                </th>

                <th style="width: 8%;">
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
                        === 'bill'
                            ? 'transaction-bill'
                            : 'transaction-payment';

                    $balanceClass =
                        match (
                            $row['balance_key']
                        ) {
                            'payable' =>
                                'balance-payable',

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
                        supplier transaction nahi mila.
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

        Positive closing balance company ki supplier
        payable liability ko represent karta hai.
        Negative closing balance supplier advance ya
        prepayment ko represent karta hai. Kisi
        discrepancy ki surat mein accounts department
        se rabta karein.
    </div>
</body>
</html>