<x-filament-panels::page>
    @php
        $partyType = request('party_type', 'Customer');
        $parties = $partyType === 'Supplier' ? $this->suppliers() : $this->customers();
        $rows = $this->rows();

        $totalDebit = $rows->sum('debit');
        $totalCredit = $rows->sum('credit');
        $closingBalance = $rows->last()['balance'] ?? 0;
    @endphp

    <div class="kes-report-card">
        <div class="kes-report-head">
            <div>
                <h2>Accounting Party Ledger</h2>
                <p>Customer / supplier ledger with debit, credit and running balance.</p>
            </div>

            <button onclick="window.print()">Print</button>
        </div>

        <form method="GET" class="kes-report-filters">
            <select name="party_type" onchange="this.form.submit()">
                <option value="Customer" @selected($partyType === 'Customer')>Customer Ledger</option>
                <option value="Supplier" @selected($partyType === 'Supplier')>Supplier Ledger</option>
            </select>

            <select name="party_id">
                <option value="">All {{ $partyType }}s</option>
                @foreach ($parties as $party)
                    <option value="{{ $party->id }}" @selected((int) request('party_id') === (int) $party->id)>
                        {{ $party->name }} {{ $party->mobile ? ' - ' . $party->mobile : '' }}
                    </option>
                @endforeach
            </select>

            <input type="date" name="from_date" value="{{ request('from_date') }}">
            <input type="date" name="to_date" value="{{ request('to_date') }}">

            <button type="submit">Apply</button>
            <a href="{{ url()->current() }}">Reset</a>
        </form>

        <div class="kes-report-summary-grid">
            <div class="kes-report-summary-card">
                <span>Party</span>
                <strong>{{ $this->selectedPartyName() }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Total Debit</span>
                <strong>PKR {{ number_format($totalDebit, 2) }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Total Credit</span>
                <strong>PKR {{ number_format($totalCredit, 2) }}</strong>
            </div>
        </div>

        <div class="kes-report-net {{ $closingBalance >= 0 ? 'profit' : 'loss' }}">
            <span>Closing Balance</span>
            <strong>
                @if ($closingBalance > 0)
                    PKR {{ number_format(abs($closingBalance), 2) }} Dr
                @elseif ($closingBalance < 0)
                    PKR {{ number_format(abs($closingBalance), 2) }} Cr
                @else
                    PKR 0.00
                @endif
            </strong>
        </div>

        <br>

        <div class="kes-report-table-wrap">
            <table class="kes-report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Ref No</th>
                        <th>Party</th>
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
                            <td>{{ $row['ref'] }}</td>
                            <td>{{ $row['party'] }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td class="right">PKR {{ number_format($row['debit'], 2) }}</td>
                            <td class="right">PKR {{ number_format($row['credit'], 2) }}</td>
                            <td class="right">{{ $row['balance_text'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No ledger records found.</td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="5">Total</th>
                        <th class="right">PKR {{ number_format($totalDebit, 2) }}</th>
                        <th class="right">PKR {{ number_format($totalCredit, 2) }}</th>
                        <th class="right">
                            @if ($closingBalance > 0)
                                PKR {{ number_format(abs($closingBalance), 2) }} Dr
                            @elseif ($closingBalance < 0)
                                PKR {{ number_format(abs($closingBalance), 2) }} Cr
                            @else
                                PKR 0.00
                            @endif
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>