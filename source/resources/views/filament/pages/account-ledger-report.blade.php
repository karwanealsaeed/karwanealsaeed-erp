<x-filament-panels::page>
    @php
        $accounts = $this->accounts();
        $selectedAccount = $this->selectedAccount();
        $rows = $this->rows();
    @endphp

    <div class="kes-report-card">
        <div class="kes-report-head">
            <div>
                <h2>Account Ledger Report</h2>
                <p>Select account and date range to view ledger.</p>
            </div>

            <x-report-actions />
        </div>

        <form method="GET" class="kes-report-filters">
            <select name="account_id" required>
                <option value="">Select Account</option>
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

        @if ($selectedAccount)
            <h3>{{ $selectedAccount->display_name }}</h3>
        @endif

        <div class="kes-report-table-wrap">
            <table class="kes-report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Voucher No</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="right">Debit</th>
                        <th class="right">Credit</th>
                        <th class="right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['voucher_no'] }}</td>
                            <td>{{ $row['type'] }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td class="right">PKR {{ number_format($row['debit'], 2) }}</td>
                            <td class="right">PKR {{ number_format($row['credit'], 2) }}</td>
                            <td class="right">PKR {{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No ledger entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>