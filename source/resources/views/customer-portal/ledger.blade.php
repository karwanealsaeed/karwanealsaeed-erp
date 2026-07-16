@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Customer Ledger</h1>
            <p>Your complete debit, credit and balance history.</p>
        </div>
    </div>

    <div class="cp-stats-grid">
        <div class="cp-stat-card">
            <span>Closing Balance</span>
            <strong>PKR {{ number_format(abs((float) $closingBalance), 2) }} {{ $closingBalance > 0 ? 'Dr' : ($closingBalance < 0 ? 'Cr' : '') }}</strong>
        </div>
    </div>

    <div class="cp-table-card">
        <table class="cp-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="right">Debit</th>
                    <th class="right">Credit</th>
                    <th class="right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                        <td>{{ $row['type'] }}</td>
                        <td>{{ $row['reference'] }}</td>
                        <td>{{ $row['description'] }}</td>
                        <td class="right">PKR {{ number_format($row['debit'], 2) }}</td>
                        <td class="right">PKR {{ number_format($row['credit'], 2) }}</td>
                        <td class="right">PKR {{ number_format(abs($row['balance']), 2) }} {{ $row['balance'] > 0 ? 'Dr' : ($row['balance'] < 0 ? 'Cr' : '') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No ledger record found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection