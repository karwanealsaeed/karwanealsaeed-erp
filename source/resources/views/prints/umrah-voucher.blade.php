<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Umrah Voucher - {{ $voucher->voucher_no }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            padding: 14mm;
        }

        .brand-bar {
            border-radius: 18px;
            background: linear-gradient(135deg, #fff7d6, #ffffff);
            border: 2px solid #c08a36;
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .brand-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #fff3c4;
            border: 2px solid #c08a36;
            display: grid;
            place-items: center;
            font-weight: 900;
            color: #8b4a1c;
            font-size: 26px;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 900;
            color: #8b4a1c;
            text-transform: uppercase;
        }

        .brand-subtitle {
            margin-top: 4px;
            color: #14532d;
            font-weight: 700;
        }

        .voucher-title {
            text-align: center;
            margin: 22px 0;
            font-size: 28px;
            font-weight: 900;
            color: #111827;
        }

        .summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 20px;
        }

        .summary-box {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            background: #fbfbfb;
        }

        .summary-box h3 {
            margin: 0 0 10px;
            color: #14532d;
            font-size: 16px;
        }

        .line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 5px 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 13px;
        }

        .line:last-child {
            border-bottom: 0;
        }

        .label {
            color: #6b7280;
            font-weight: 700;
        }

        .value {
            font-weight: 800;
            text-align: right;
        }

        .section-title {
            text-align: center;
            margin: 22px 0 10px;
            font-size: 19px;
            font-weight: 900;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 12px;
        }

        th {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-weight: 900;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
        }

        .notes {
            margin-top: 18px;
            font-size: 13px;
            line-height: 1.7;
        }

        .footer {
            margin-top: 24px;
            border-radius: 14px;
            padding: 14px;
            background: #f9fafb;
            text-align: center;
            color: #6b7280;
            font-weight: 700;
        }

        .watermark {
            position: fixed;
            inset: 0;
            display: grid;
            place-items: center;
            pointer-events: none;
            opacity: 0.05;
            font-size: 90px;
            font-weight: 900;
            color: #14532d;
            transform: rotate(-25deg);
        }

        .print-actions {
            width: 210mm;
            margin: 16px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .print-btn {
            border: 0;
            background: #14532d;
            color: white;
            padding: 10px 16px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 800;
        }

        @media print {
            body {
                background: white;
            }

            .print-actions {
                display: none;
            }

            .page {
                width: 100%;
                min-height: auto;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="page">
        <div class="watermark">KARWAN E AL SAEED</div>

        <div class="brand-bar">
            <div class="brand-left">
                <div class="brand-logo">KES</div>

                <div>
                    <div class="brand-title">{{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}</div>
                    <div class="brand-subtitle">Your Trusted Travel Partner for Umrah, Visas, Tickets & Tours</div>
                </div>
            </div>

            <div style="text-align:right; font-size:13px; font-weight:700;">
                <div>{{ $company->mobile ?? '+92 301 2592342' }}</div>
                <div>{{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}</div>
            </div>
        </div>

        <div class="voucher-title">Umrah Voucher</div>

        <div class="summary">
            <div class="summary-box">
                <h3>Lead Guest</h3>
                <div class="line">
                    <span class="label">Name</span>
                    <span class="value">{{ $voucher->lead_guest ?: '-' }}</span>
                </div>
                <div class="line">
                    <span class="label">Phone</span>
                    <span class="value">{{ $voucher->phone ?: '-' }}</span>
                </div>
                <div class="line">
                    <span class="label">Package</span>
                    <span class="value">{{ $package?->package_no ?? '-' }}</span>
                </div>
            </div>

            <div class="summary-box">
                <h3>Voucher Details</h3>
                <div class="line">
                    <span class="label">Voucher No</span>
                    <span class="value">{{ $voucher->voucher_no }}</span>
                </div>
                <div class="line">
                    <span class="label">Travelers</span>
                    <span class="value">{{ $voucher->travelers_text }}</span>
                </div>
                <div class="line">
                    <span class="label">PNR</span>
                    <span class="value">{{ $voucher->pnr ?: '-' }}</span>
                </div>
            </div>
        </div>

        <div class="section-title">Passengers</div>

        <table>
            <thead>
                <tr>
                    <th style="width:32px;">#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Gender</th>
                    <th>Nationality</th>
                    <th>Passport No</th>
                    <th>Date of Birth</th>
                    <th>Ticket No</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($voucher->passengers_list as $index => $passenger)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $passenger['name'] ?? '-' }}</td>
                        <td>{{ $passenger['type'] ?? '-' }}</td>
                        <td>{{ $passenger['gender'] ?? '-' }}</td>
                        <td>{{ $passenger['nationality'] ?? '-' }}</td>
                        <td>{{ $passenger['passport_no'] ?? '-' }}</td>
                        <td>{{ filled($passenger['date_of_birth'] ?? null) ? \Carbon\Carbon::parse($passenger['date_of_birth'])->format('d-M-Y') : '-' }}</td>
                        <td>{{ $passenger['ticket_no'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No passenger details added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Hotels</div>

        <table>
            <thead>
                <tr>
                    <th>City</th>
                    <th>Hotel</th>
                    <th>Room Type</th>
                    <th>Distance</th>
                    <th>Location</th>
                    <th>Nights</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>MAK</td>
                    <td>
                        <strong>{{ $package?->makkah_hotel ?: '-' }}</strong><br>
                        {{ $package?->makkahHotel?->rating ?: '' }}
                    </td>
                    <td>{{ $voucher->room_type ?: '-' }}</td>
                    <td>{{ $package?->makkah_distance ?: '-' }}</td>
                    <td>{{ $package?->makkah_location ?: '-' }}</td>
                    <td>{{ $package?->makkah_nights ?: '-' }}</td>
                </tr>

                <tr>
                    <td>MED</td>
                    <td>
                        <strong>{{ $package?->madina_hotel ?: '-' }}</strong><br>
                        {{ $package?->madinaHotel?->rating ?: '' }}
                    </td>
                    <td>{{ $voucher->room_type ?: '-' }}</td>
                    <td>{{ $package?->madina_distance ?: '-' }}</td>
                    <td>{{ $package?->madina_location ?: '-' }}</td>
                    <td>{{ $package?->madina_nights ?: '-' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Transport</div>

        <table>
            <thead>
                <tr>
                    <th>Transport Type</th>
                    <th>Vehicle</th>
                    <th>Route / Description</th>
                    <th>Booking No</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $package?->transportRoute?->transport_type ?: '-' }}</td>
                    <td>{{ $package?->transportRoute?->vehicle_type ?: '-' }}</td>
                    <td>{{ $package?->transport ?: '-' }}</td>
                    <td>{{ $voucher->booking_reference ?: '-' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Flights</div>

        <table>
            <thead>
                <tr>
                    <th>Flight</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Departure Date</th>
                    <th>Depart</th>
                    <th>Arrival Date</th>
                    <th>Arrive</th>
                    <th>PNR</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $segments = is_array($groupFlight?->flight_segments) ? $groupFlight->flight_segments : [];
                @endphp

                @forelse ($segments as $segment)
                    <tr>
                        <td>{{ $segment['flight_no'] ?? '-' }}</td>
                        <td>{{ $segment['from'] ?? '-' }}</td>
                        <td>{{ $segment['to'] ?? '-' }}</td>
                        <td>{{ filled($segment['departure_date'] ?? null) ? \Carbon\Carbon::parse($segment['departure_date'])->format('d-M-Y') : '-' }}</td>
                        <td>{{ $segment['departure_time'] ?? '-' }}</td>
                        <td>{{ filled($segment['arrival_date'] ?? null) ? \Carbon\Carbon::parse($segment['arrival_date'])->format('d-M-Y') : '-' }}</td>
                        <td>{{ $segment['arrival_time'] ?? '-' }}</td>
                        <td>{{ $voucher->pnr ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No flight segments added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($voucher->remarks)
            <div class="notes">
                <strong>Remarks:</strong><br>
                {!! nl2br(e($voucher->remarks)) !!}
            </div>
        @endif

        @if ($voucher->special_instructions)
            <div class="notes">
                <strong>Special Instructions:</strong><br>
                {!! nl2br(e($voucher->special_instructions)) !!}
            </div>
        @endif

        @if ($voucher->terms_conditions)
            <div class="notes">
                <strong>Terms & Conditions:</strong><br>
                {!! nl2br(e($voucher->terms_conditions)) !!}
            </div>
        @endif

        <div class="footer">
            {{ $company->mobile ?? '+92 301 2592342' }} |
            {{ $company->whatsapp ?? '+92 301 2592342' }} |
            {{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}
        </div>
    </div>
</body>
</html>