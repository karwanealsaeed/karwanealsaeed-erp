<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Payment Receipt {{ $receipt['receipt_no'] }}</title>

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

        .receipt-title {
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

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-table th,
        .info-table td {
            border: 1px solid #d1d5db;
            padding: 9px;
            vertical-align: top;
        }

        .info-table th {
            width: 25%;
            background: #f9fafb;
            text-align: left;
        }

        .amount-box {
            margin-top: 18px;
            border: 2px solid #111827;
            padding: 14px;
            text-align: center;
        }

        .amount-label {
            font-size: 14px;
            color: #374151;
            margin-bottom: 5px;
        }

        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #16a34a;
        }

        .signature-row {
            width: 100%;
            margin-top: 55px;
            border-collapse: collapse;
        }

        .signature-row td {
            border: none;
            width: 50%;
            padding-top: 35px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #111827;
            display: inline-block;
            width: 220px;
            padding-top: 6px;
            font-size: 12px;
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

        <div class="receipt-title">
            PAYMENT RECEIPT
        </div>

        <div class="section-title">
            Receipt Information
        </div>

        <table class="info-table">
            <tr>
                <th>Receipt No</th>
                <td>{{ $receipt['receipt_no'] }}</td>

                <th>Payment Date</th>
                <td>
                    {{ $receipt['payment_date'] ? \Carbon\Carbon::parse($receipt['payment_date'])->format('d M Y') : '-' }}
                </td>
            </tr>

            <tr>
                <th>Service</th>
                <td>{{ $receipt['service'] }}</td>

                <th>Service Reference</th>
                <td>{{ $receipt['reference_no'] }}</td>
            </tr>

            <tr>
                <th>Payment Method</th>
                <td>{{ $receipt['method'] }}</td>

                <th>Reference No</th>
                <td>{{ $receipt['external_reference'] }}</td>
            </tr>
        </table>

        <div class="section-title">
            Customer Information
        </div>

        <table class="info-table">
            <tr>
                <th>Customer Name</th>
                <td>{{ $receipt['customer_name'] }}</td>

                <th>Mobile</th>
                <td>{{ $receipt['customer_mobile'] }}</td>
            </tr>

            <tr>
                <th>Notes</th>
                <td colspan="3">{{ $receipt['notes'] }}</td>
            </tr>
        </table>

        <div class="amount-box">
            <div class="amount-label">
                Amount Received
            </div>

            <div class="amount-value">
                PKR {{ number_format($receipt['amount'] ?? 0, 2) }}
            </div>
        </div>

        <table class="signature-row">
            <tr>
                <td>
                    <span class="signature-line">Customer Signature</span>
                </td>

                <td>
                    <span class="signature-line">Authorized Signature</span>
                </td>
            </tr>
        </table>

        <div class="footer">
            This receipt was generated from {{ $company->company_name ?? 'Karwan E Al Saeed ERP' }}.
        </div>

        <div class="actions">
            <button type="button" class="print-button" onclick="window.print()">
                Print Receipt
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