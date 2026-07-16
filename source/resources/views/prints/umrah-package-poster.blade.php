<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Umrah Packages Poster</title>

    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #08251a;
        }

        .screen-actions {
            width: 297mm;
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

        .poster-page {
            position: relative;
            width: 297mm;
            min-height: 210mm;
            margin: 0 auto 18px;
            overflow: hidden;
            background:
                radial-gradient(circle at top left, rgba(213, 169, 79, 0.18), transparent 35rem),
                radial-gradient(circle at bottom right, rgba(7, 59, 40, 0.12), transparent 35rem),
                #fbfaf6;
            border: 1px solid #eadfca;
        }

        .poster-page::before {
            content: "";
            position: absolute;
            right: -80px;
            top: -100px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            border: 36px solid rgba(213, 169, 79, 0.18);
        }

        .poster-header {
            position: relative;
            z-index: 2;
            padding: 18px 26px;
            color: white;
            background:
                linear-gradient(135deg, #031d14, #073b28),
                radial-gradient(circle at top right, rgba(213, 169, 79, 0.24), transparent 20rem);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            display: grid;
            place-items: center;
            object-fit: contain;
            background: #fff8dc;
            border: 2px solid #d5a94f;
            color: #073b28;
            font-size: 20px;
            font-weight: 950;
        }

        .brand h1 {
            margin: 0;
            font-size: 30px;
            line-height: 1.05;
            color: #fff6d8;
            text-transform: uppercase;
        }

        .brand p {
            margin: 6px 0 0;
            color: rgba(255, 255, 255, 0.86);
            font-size: 14px;
            font-weight: 700;
        }

        .poster-title {
            text-align: right;
        }

        .poster-title h2 {
            margin: 0;
            color: #f1cf6a;
            font-size: 42px;
            line-height: 1;
            text-transform: uppercase;
        }

        .poster-title p {
            margin: 8px 0 0;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 800;
        }

        .poster-content {
            position: relative;
            z-index: 2;
            padding: 20px 24px 18px;
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 13px;
        }

        .package-card {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #eadfca;
            border-radius: 18px;
            box-shadow: 0 12px 26px rgba(7, 59, 40, 0.10);
        }

        .package-card-top {
            padding: 13px 14px;
            background: linear-gradient(135deg, #ffffff, #fff8dc);
            border-bottom: 1px solid #eadfca;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
        }

        .package-card-top h3 {
            margin: 0;
            color: #073b28;
            font-size: 16px;
            line-height: 1.18;
            font-weight: 950;
        }

        .package-card-top p {
            margin: 5px 0 0;
            color: #6f756f;
            font-size: 11px;
            font-weight: 850;
        }

        .duration {
            min-width: 54px;
            text-align: center;
            color: #073b28;
            background: #fff6d8;
            border: 1px solid #d5a94f;
            border-radius: 999px;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: 950;
        }

        .package-body {
            padding: 12px 14px 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 7px;
            font-size: 11px;
            font-weight: 800;
        }

        .info {
            padding: 8px;
            border-radius: 10px;
            background: #fbfaf6;
            border: 1px solid #f0e5d2;
        }

        .info strong {
            display: block;
            color: #073b28;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .hotel-line {
            margin-top: 9px;
            padding: 8px;
            border-radius: 12px;
            border: 1px solid #f0e5d2;
            background: #fbfdfb;
            font-size: 11px;
            font-weight: 800;
        }

        .hotel-line strong {
            color: #073b28;
        }

        .price-grid {
            margin-top: 10px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
        }

        .price-box {
            text-align: center;
            border-radius: 10px;
            background: #073b28;
            color: white;
            padding: 7px 3px;
        }

        .price-box .label {
            color: #f1cf6a;
            font-size: 9px;
            font-weight: 950;
            text-transform: uppercase;
        }

        .price-box .amount {
            margin-top: 3px;
            font-size: 11px;
            font-weight: 950;
        }

        .seats {
            margin-top: 9px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #073b28;
            font-size: 11px;
            font-weight: 950;
        }

        .badge {
            border-radius: 999px;
            padding: 5px 8px;
            background: #eef8f2;
            color: #073b28;
            border: 1px solid #d9ecdf;
        }

        .poster-footer {
            position: absolute;
            left: 24px;
            right: 24px;
            bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            padding: 10px 14px;
            border-radius: 16px;
            color: white;
            background: linear-gradient(135deg, #073b28, #031d14);
            font-size: 13px;
            font-weight: 850;
        }

        .text-box {
            width: 297mm;
            margin: 18px auto;
            background: white;
            border: 1px solid #eadfca;
            border-radius: 18px;
            padding: 16px;
        }

        .text-box h3 {
            margin: 0 0 10px;
            color: #073b28;
        }

        .text-box textarea {
            width: 100%;
            height: 260px;
            border: 1px solid #eadfca;
            border-radius: 14px;
            padding: 14px;
            font-family: Consolas, monospace;
            font-size: 13px;
            line-height: 1.5;
        }

        @media print {
            body {
                background: white;
            }

            .screen-actions,
            .text-box {
                display: none;
            }

            .poster-page {
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
        <button onclick="navigator.clipboard.writeText(document.getElementById('posterText').value)">Copy Text</button>
    </div>

    <div class="poster-page">
        <div class="poster-header">
            <div class="brand">
                @if (file_exists(public_path('images/logo.png')))
                    <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="Logo">
                @else
                    <div class="brand-logo">KES</div>
                @endif

                <div>
                    <h1>{{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}</h1>
                    <p>{{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}</p>
                </div>
            </div>

            <div class="poster-title">
                <h2>Umrah Packages</h2>
                <p>{{ $company->mobile ?? '+92 301 2592342' }} | {{ $company->whatsapp ?? '+92 301 2592342' }}</p>
            </div>
        </div>

        <div class="poster-content">
            @if ($packages->count() > 0)
                <div class="packages-grid">
                    @foreach ($packages as $package)
                        @php
                            $group = $package->umrahGroupFlight;
                            $routeText = $package->flight_route ?: ($group?->route ?: '-');
                            $departureDate = $group?->departure_date ? $group->departure_date->format('d M Y') : '-';
                            $returnDate = $group?->return_date ? $group->return_date->format('d M Y') : '-';
                        @endphp

                        <div class="package-card">
                            <div class="package-card-top">
                                <div>
                                    <h3>{{ $package->title }}</h3>
                                    <p>{{ $package->package_no }} | {{ $package->group_code ?: '-' }}</p>
                                </div>

                                <div class="duration">
                                    {{ $package->duration_nights ?: '-' }}N
                                </div>
                            </div>

                            <div class="package-body">
                                <div class="info-grid">
                                    <div class="info">
                                        <strong>Airline</strong>
                                        {{ $package->airline ?: '-' }}
                                    </div>

                                    <div class="info">
                                        <strong>Route</strong>
                                        {{ $routeText }}
                                    </div>

                                    <div class="info">
                                        <strong>Departure</strong>
                                        {{ $departureDate }}
                                    </div>

                                    <div class="info">
                                        <strong>Return</strong>
                                        {{ $returnDate }}
                                    </div>
                                </div>

                                <div class="hotel-line">
                                    <strong>Makkah:</strong>
                                    {{ $package->makkah_hotel ?: '-' }}
                                    / {{ $package->makkah_nights ?: '-' }}N
                                    / {{ $package->makkah_distance ?: '-' }}
                                </div>

                                <div class="hotel-line">
                                    <strong>Madinah:</strong>
                                    {{ $package->madina_hotel ?: '-' }}
                                    / {{ $package->madina_nights ?: '-' }}N
                                    / {{ $package->madina_distance ?: '-' }}
                                </div>

                                <div class="price-grid">
                                    <div class="price-box">
                                        <div class="label">Sharing</div>
                                        <div class="amount">{{ $package->sharing_price ? number_format((float) $package->sharing_price, 0) : 'N/A' }}</div>
                                    </div>

                                    <div class="price-box">
                                        <div class="label">Quad</div>
                                        <div class="amount">{{ $package->quad_price ? number_format((float) $package->quad_price, 0) : 'N/A' }}</div>
                                    </div>

                                    <div class="price-box">
                                        <div class="label">Triple</div>
                                        <div class="amount">{{ $package->triple_price ? number_format((float) $package->triple_price, 0) : 'N/A' }}</div>
                                    </div>

                                    <div class="price-box">
                                        <div class="label">Double</div>
                                        <div class="amount">{{ $package->double_price ? number_format((float) $package->double_price, 0) : 'N/A' }}</div>
                                    </div>
                                </div>

                                <div class="seats">
                                    <span class="badge">Seats: {{ $package->seats_left }} / {{ $package->seats_available }}</span>
                                    <span>PKR Per Person</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <h2>No packages found.</h2>
            @endif
        </div>

        <div class="poster-footer">
            <div>Book Now: {{ $company->mobile ?? '+92 301 2592342' }}</div>
            <div>{{ $company->company_name ?? 'Karwan E Al Saeed Travel & Tours' }}</div>
            <div>{{ $company->whatsapp ?? '+92 301 2592342' }}</div>
        </div>
    </div>

    <div class="text-box">
        <h3>Text Format For WhatsApp / Facebook</h3>
        <textarea id="posterText" readonly>{{ $posterText }}</textarea>
    </div>
</body>
</html>