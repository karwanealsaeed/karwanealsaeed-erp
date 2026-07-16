<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salary Slip - {{ $payroll->payroll_no }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            color: #08251a;
            font-family: Arial, Helvetica, sans-serif;
        }

        .screen-actions {
            width: 210mm;
            margin: 14px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .screen-actions button {
            border: 0;
            border-radius: 999px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: 900;
            color: white;
            background: #073b28;
        }

        .slip {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fbfaf6;
            border: 1px solid #eadfca;
            padding: 22px;
            position: relative;
            overflow: hidden;
        }

        .slip::before {
            content: "";
            position: absolute;
            right: -110px;
            top: -110px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            border: 42px solid rgba(213, 169, 79, 0.18);
        }

        .header {
            position: relative;
            z-index: 2;
            padding: 18px;
            border-radius: 22px;
            color: white;
            background: linear-gradient(135deg, #031d14, #073b28);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            object-fit: contain;
            color: #073b28;
            background: #fff8dc;
            border: 2px solid #d5a94f;
            font-weight: 950;
        }

        .brand h1 {
            margin: 0;
            color: #fff6d8;
            font-size: 25px;
            line-height: 1.1;
        }

        .brand p {
            margin: 6px 0 0;
            color: rgba(255,255,255,.82);
            font-size: 13px;
            font-weight: 700;
        }

        .title {
            text-align: right;
        }

        .title h2 {
            margin: 0;
            color: #f1cf6a;
            font-size: 32px;
            text-transform: uppercase;
        }

        .title p {
            margin: 7px 0 0;
            font-weight: 800;
        }

        .content {
            position: relative;
            z-index: 2;
            padding-top: 22px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .box {
            background: #ffffff;
            border: 1px solid #eadfca;
            border-radius: 18px;
            padding: 14px;
        }

        .box h3 {
            margin: 0 0 12px;
            color: #073b28;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #f1eadc;
            text-align: left;
            font-size: 13px;
        }

        th {
            width: 42%;
            color: #6f756f;
            font-weight: 950;
        }

        td {
            color: #08251a;
            font-weight: 800;
        }

        .salary-table {
            margin-top: 16px;
            background: #ffffff;
            border: 1px solid #eadfca;
            border-radius: 18px;
            overflow: hidden;
        }

        .salary-table th {
            background: #fff8dc;
            color: #073b28;
        }

        .right {
            text-align: right;
        }

        .net {
            margin-top: 18px;
            padding: 18px;
            border-radius: 18px;
            color: #031d14;
            background: linear-gradient(135deg, #d5a94f, #f1cf6a);
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-weight: 950;
            font-size: 20px;
        }

        .footer {
            position: absolute;
            left: 22px;
            right: 22px;
            bottom: 22px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid #eadfca;
            color: #6f756f;
            font-size: 12px;
            font-weight: 800;
        }

        .signature {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
        }

        .signature div {
            border-top: 1px solid #08251a;
            padding-top: 8px;
            text-align: center;
            font-weight: 900;
        }

        @media print {
            body {
                background: white;
            }

            .screen-actions {
                display: none;
            }

            .slip {
                width: 100%;
                min-height: 100vh;
                margin: 0;
                border: 0;
            }
        }
    </style>
</head>
<body>
    <div class="screen-actions">
        <button onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="slip">
        <div class="header">
            <div class="brand">
                @if (file_exists(public_path('images/logo.png')))
                    <img class="logo" src="{{ asset('images/logo.png') }}" alt="Logo">
                @else
                    <div class="logo">KES</div>
                @endif

                <div>
                    <h1>{{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}</h1>
                    <p>{{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}</p>
                </div>
            </div>

            <div class="title">
                <h2>Salary Slip</h2>
                <p>{{ $payroll->payroll_no }}</p>
            </div>
        </div>

        <div class="content">
            <div class="grid">
                <div class="box">
                    <h3>Employee Details</h3>
                    <table>
                        <tr><th>Employee No</th><td>{{ $payroll->staffMember?->employee_no ?? '-' }}</td></tr>
                        <tr><th>Name</th><td>{{ $payroll->staffMember?->name ?? '-' }}</td></tr>
                        <tr><th>Designation</th><td>{{ $payroll->staffMember?->designation ?? '-' }}</td></tr>
                        <tr><th>Department</th><td>{{ $payroll->staffMember?->department ?? '-' }}</td></tr>
                        <tr><th>Mobile</th><td>{{ $payroll->staffMember?->mobile ?? '-' }}</td></tr>
                    </table>
                </div>

                <div class="box">
                    <h3>Payroll Details</h3>
                    <table>
                        <tr><th>Payroll No</th><td>{{ $payroll->payroll_no }}</td></tr>
                        <tr><th>Salary Month</th><td>{{ $payroll->period_text }}</td></tr>
                        <tr><th>Payment Date</th><td>{{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : '-' }}</td></tr>
                        <tr><th>Payment Method</th><td>{{ $payroll->payment_method ?? '-' }}</td></tr>
                        <tr><th>Status</th><td>{{ $payroll->status }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="salary-table">
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="right">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr><td>Basic Salary</td><td class="right">PKR {{ number_format((float) $payroll->basic_salary, 2) }}</td></tr>
                        <tr><td>Allowance</td><td class="right">PKR {{ number_format((float) $payroll->allowance, 2) }}</td></tr>
                        <tr><td>Bonus</td><td class="right">PKR {{ number_format((float) $payroll->bonus, 2) }}</td></tr>
                        <tr><td>Deduction</td><td class="right">PKR {{ number_format((float) $payroll->deduction, 2) }}</td></tr>
                        <tr><td>Advance</td><td class="right">PKR {{ number_format((float) $payroll->advance, 2) }}</td></tr>
                        <tr><td>Paid Amount</td><td class="right">PKR {{ number_format((float) $payroll->paid_amount, 2) }}</td></tr>
                        <tr><td>Balance Amount</td><td class="right">PKR {{ number_format((float) $payroll->balance_amount, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="net">
                <span>Net Salary</span>
                <strong>PKR {{ number_format((float) $payroll->net_salary, 2) }}</strong>
            </div>

            @if ($payroll->remarks)
                <div class="box" style="margin-top:16px;">
                    <h3>Remarks</h3>
                    <p>{{ $payroll->remarks }}</p>
                </div>
            @endif

            <div class="signature">
                <div>Employee Signature</div>
                <div>Authorized Signature</div>
            </div>
        </div>

        <div class="footer">
            <span>{{ $company->mobile ?? '+92 301 2592342' }}</span>
            <span>Generated on {{ now('Asia/Karachi')->format('d M Y h:i A') }}</span>
        </div>
    </div>
</body>
</html>