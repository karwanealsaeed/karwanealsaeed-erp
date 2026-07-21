@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Customer Ledger</h1>
            <p>
                Authoritative invoice, approved receipt and refund history.
            </p>
        </div>
    </div>

    @if (! ($statement['database_ready'] ?? false))
        <div class="cp-card">
            <strong>Ledger database is not ready.</strong>

            <ul>
                @foreach (($statement['missing_requirements'] ?? []) as $requirement)
                    <li>{{ $requirement }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="cp-stats-grid">
            <div class="cp-stat-card">
                <span>Opening Balance</span>
                <strong>
                    PKR {{ number_format(abs((float) ($statement['stats']['opening_balance'] ?? 0)), 2) }}
                </strong>
            </div>

            <div class="cp-stat-card">
                <span>Total Debit</span>
                <strong>
                    PKR {{ number_format((float) ($statement['stats']['period_debit'] ?? 0), 2) }}
                </strong>
            </div>

            <div class="cp-stat-card">
                <span>Total Credit</span>
                <strong>
                    PKR {{ number_format((float) ($statement['stats']['period_credit'] ?? 0), 2) }}
                </strong>
            </div>

            <div class="cp-stat-card">
                <span>Closing Balance</span>
                <strong>
                    PKR {{ number_format(abs((float) $closingBalance), 2) }}
                    {{ $closingBalance > 0 ? 'Dr' : ($closingBalance < 0 ? 'Cr' : '') }}
                </strong>

                <small>
                    {{ $statement['closing_status'] ?? 'Settled' }}
                </small>
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
                        @php
                            $runningBalance = (float) ($row['running_balance'] ?? 0);
                        @endphp

                        <tr>
                            <td>
                                {{ $row['date'] ?? '-' }}
                            </td>

                            <td>
                                {{ $row['transaction_type'] ?? '-' }}
                            </td>

                            <td>
                                {{ $row['source_no'] ?? '-' }}
                            </td>

                            <td>
                                {{ $row['description'] ?? '-' }}
                            </td>

                            <td class="right">
                                PKR {{ number_format((float) ($row['debit'] ?? 0), 2) }}
                            </td>

                            <td class="right">
                                PKR {{ number_format((float) ($row['credit'] ?? 0), 2) }}
                            </td>

                            <td class="right">
                                PKR {{ number_format(abs($runningBalance), 2) }}
                                {{ $runningBalance > 0 ? 'Dr' : ($runningBalance < 0 ? 'Cr' : '') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                No ledger record found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection