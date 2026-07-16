<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>{{ $title }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111827;
            font-size: 11px;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .page {
            width: 100%;
            background: #ffffff;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .logo-cell {
            width: 90px;
        }

        .logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
        }

        .company-name {
            font-size: 22px;
            line-height: 1.2;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }

        .company-detail {
            font-size: 10px;
            line-height: 1.4;
            color: #374151;
        }

        .report-title {
            margin-top: 6px;
            padding: 7px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }

        .generated {
            margin-top: 4px;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }

        h2 {
            font-size: 14px;
            margin: 14px 0 6px 0;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px;
            vertical-align: top;
            font-size: 10px;
            line-height: 1.25;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        .summary-table th {
            width: 35%;
        }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }

        .print-actions {
            margin-top: 14px;
            text-align: right;
        }

        .print-button {
            border: none;
            background: #111827;
            color: #ffffff;
            padding: 8px 14px;
            font-size: 13px;
            border-radius: 6px;
            cursor: pointer;
        }

        @media print {
            .print-actions {
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
                    @if ($logoSrc)
                        <img src="{{ $logoSrc }}" class="logo" alt="Logo">
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

        <div class="report-title">
            {{ $title }}
        </div>

        <div class="generated">
            Generated At: {{ $generatedAt }}
        </div>

        @if (! empty($exportData['summary']))
            <h2>Summary</h2>

            <table class="summary-table">
                <tbody>
                    @foreach ($exportData['summary'] as $label => $value)
                        <tr>
                            <th>{{ $label }}</th>
                            <td>{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @foreach ($exportData['tables'] as $table)
            <h2>{{ $table['title'] }}</h2>

            @if (! empty($table['rows']))
                <table>
                    <thead>
                        <tr>
                            @foreach (array_keys($table['rows'][0]) as $heading)
                                <th>{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($table['rows'] as $row)
                            <tr>
                                @foreach ($row as $value)
                                    <td>{{ $value }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No data found.</p>
            @endif
        @endforeach

        <div class="footer">
            This report was generated from {{ $company->company_name ?? 'Karwan E Al Saeed ERP' }}.
        </div>

        @if (! empty($isPrint))
            <div class="print-actions">
                <button type="button" class="print-button" onclick="window.print()">
                    Print
                </button>
            </div>

            <script>
                window.addEventListener('load', function () {
                    setTimeout(function () {
                        window.print();
                    }, 500);
                });
            </script>
        @endif
    </div>
</body>
</html>