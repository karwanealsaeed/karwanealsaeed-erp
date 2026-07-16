<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Invoice {{ $invoice->invoice_no }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 13px;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .page {
            width: 100%;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .header-table td {
            vertical-align: top;
            border: none;
            padding: 0;
        }

        .logo-cell {
            width: 95px;
        }

        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-detail {
            font-size: 12px;
            line-height: 1.5;
            color: #374151;
        }

        .invoice-title {
            text-align: center;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 10px;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            margin: 18px 0 8px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #d1d5db;
        }

        .info-table,
        .amount-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-table th,
        .info-table td,
        .amount-table th,
        .amount-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
        }

        .info-table th {
            width: 25%;
            background: #f9fafb;
            text-align: left;
        }

        .amount-table th {
            background: #f3f4f6;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .grand-total {
            background: #f3f4f6;
            font-weight: bold;
            font-size: 15px;
        }

        .balance {
            color: #dc2626;
            font-weight: bold;
        }

        .paid {
            color: #16a34a;
            font-weight: bold;
        }

        .footer {
            margin-top: 35px;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            font-size: 11px;
            color: #6b7280;
            text-align: center;
        }

        .actions {
            margin-top: 20px;
            text-align: right;
        }

        .print-button {
            border: none;
            background: #111827;
            color: #ffffff;
            padding: 9px 16px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
        }

        @media print {
            .actions {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" class="logo" alt="Logo">
                    @endif
                </td>

                <td>
                    <div class="company-name">
                        {{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}
                    </div>

                    <div class="company-detail">
                        @if ($company->address)
                            {{ $company->address }}<br>
                        @endif

                        @if ($company->city || $company->country)
                            {{ $company->city }}{{ $company->city && $company->country ? ', ' : '' }}{{ $company->country }}<br>
                        @endif

                        @if ($company->phone || $company->mobile || $company->whatsapp)
                            Phone:
                            {{ $company->phone ?? '' }}
                            {{ $company->mobile ? ' | Mobile: ' . $company->mobile : '' }}
                            {{ $company->whatsapp ? ' | WhatsApp: ' . $company->whatsapp : '' }}
                            <br>
                        @endif

                        @if ($company->email || $company->website)
                            {{ $company->email ? 'Email: ' . $company->email : '' }}
                            {{ $company->website ? ' | Website: ' . $company->website : '' }}
                            <br>
                        @endif

                        @if ($company->ntn_no || $company->license_no)
                            {{ $company->ntn_no ? 'NTN: ' . $company->ntn_no : '' }}
                            {{ $company->license_no ? ' | License: ' . $company->license_no : '' }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="invoice-title">
            INVOICE
        </div>

        <div class="section-title">
            Invoice Information
        </div>

        <table class="info-table">
            <tr>
                <th>Invoice No</th>
                <td>{{ $invoice->invoice_no ?? '-' }}</td>

                <th>Invoice Date</th>
                <td>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') : '-' }}</td>
            </tr>

            <tr>
                <th>Due Date</th>
                <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</td>

                <th>Status</th>
                <td>{{ $invoice->status ?? '-' }}</td>
            </tr>

            <tr>
                <th>Service Type</th>
                <td>{{ $invoice->service_type ?? '-' }}</td>

                <th>Service Reference</th>
                <td>{{ $invoice->service_reference ?? '-' }}</td>
            </tr>
        </table>

        <div class="section-title">
            Customer Information
        </div>

        <table class="info-table">
            <tr>
                <th>Customer Name</th>
                <td>{{ $invoice->customer?->name ?? '-' }}</td>

                <th>Mobile</th>
                <td>{{ $invoice->customer?->mobile ?? '-' }}</td>
            </tr>

            <tr>
                <th>CNIC</th>
                <td>{{ $invoice->customer?->cnic ?? '-' }}</td>

                <th>Passport No</th>
                <td>{{ $invoice->customer?->passport_no ?? '-' }}</td>
            </tr>

            <tr>
                <th>Address</th>
                <td colspan="3">{{ $invoice->customer?->address ?? '-' }}</td>
            </tr>
        </table>

        <div class="section-title">
            Amount Summary
        </div>

        <table class="amount-table">
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>

            <tr>
                <td>Sub Total</td>
                <td class="text-right">PKR {{ number_format($invoice->sub_total ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Discount</td>
                <td class="text-right">PKR {{ number_format($invoice->discount ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Tax</td>
                <td class="text-right">PKR {{ number_format($invoice->tax ?? 0, 2) }}</td>
            </tr>

            <tr class="grand-total">
                <td>Total Amount</td>
                <td class="text-right">PKR {{ number_format($invoice->total_amount ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td class="paid">Paid Amount</td>
                <td class="text-right paid">PKR {{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td class="balance">Balance Amount</td>
                <td class="text-right balance">PKR {{ number_format($invoice->balance_amount ?? 0, 2) }}</td>
            </tr>
        </table>

        @if ($invoice->notes)
            <div class="section-title">
                Notes
            </div>

            <p>{{ $invoice->notes }}</p>
        @endif

        <div class="footer">
            This invoice was generated from {{ $company->company_name ?? 'Karwan E Al Saeed ERP' }}.
        </div>

        <div class="actions">
            <button type="button" class="print-button" onclick="window.print()">
                Print Invoice
            </button>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>