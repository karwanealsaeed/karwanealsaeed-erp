<x-filament-panels::page>
    @php
        $accounts = $this->accounts();
        $selectedAccount = $this->selectedAccount();
        $rows = $this->rows();

        $totalDebit = $rows->sum('debit');
        $totalCredit = $rows->sum('credit');
        $closingBalance = $rows->last()['balance'] ?? 0;
    @endphp

    <div class="kes-report-card">
        <div class="kes-report-head">
            <div>
                <h2>Cash / Bank Book Report</h2>
                <p>Cash and bank account movement.</p>
            </div>

            <button onclick="window.print()">Print</button>
        </div>

        <form method="GET" class="kes-report-filters">
            <select name="account_id">
                <option value="">All Cash / Bank Accounts</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected((int) request('account_id') === $account->id)>
                        {{ $account->display_name }}
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
                <span>Total In</span>
                <strong>PKR {{ number_format($totalDebit, 2) }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Total Out</span>
                <strong>PKR {{ number_format($totalCredit, 2) }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Closing Balance</span>
                <strong>PKR {{ number_format($closingBalance, 2) }}</strong>
            </div>
        </div>

        @if ($selectedAccount)
            <h3>{{ $selectedAccount->display_name }}</h3>
        @endif

        <div class="kes-report-table-wrap">
            <table class="kes-report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Account</th>
                        <th>Voucher No</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="right">In</th>
                        <th class="right">Out</th>
                        <th class="right">Balance</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['account'] }}</td>
                            <td>{{ $row['voucher_no'] }}</td>
                            <td>{{ $row['type'] }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td class="right">PKR {{ number_format($row['debit'], 2) }}</td>
                            <td class="right">PKR {{ number_format($row['credit'], 2) }}</td>
                            <td class="right">PKR {{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No cash/bank entries found.</td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="5">Total</th>
                        <th class="right">PKR {{ number_format($totalDebit, 2) }}</th>
                        <th class="right">PKR {{ number_format($totalCredit, 2) }}</th>
                        <th class="right">PKR {{ number_format($closingBalance, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>