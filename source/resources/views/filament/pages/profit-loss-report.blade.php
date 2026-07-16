<x-filament-panels::page>
    @php
        $incomeRows = $this->incomeRows();
        $expenseRows = $this->expenseRows();

        $totalIncome = $incomeRows->sum('amount');
        $totalExpense = $expenseRows->sum('amount');
        $netProfit = $totalIncome - $totalExpense;
    @endphp

    <div class="kes-report-card">
        <div class="kes-report-head">
            <div>
                <h2>Profit & Loss Report</h2>
                <p>Income, expenses and net profit/loss.</p>
            </div>

            <button onclick="window.print()">Print</button>
        </div>

        <form method="GET" class="kes-report-filters">
            <input type="date" name="from_date" value="{{ request('from_date') }}">
            <input type="date" name="to_date" value="{{ request('to_date') }}">
            <button type="submit">Apply</button>
            <a href="{{ url()->current() }}">Reset</a>
        </form>

        <div class="kes-report-summary-grid">
            <div class="kes-report-summary-card">
                <span>Total Income</span>
                <strong>PKR {{ number_format($totalIncome, 2) }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Total Expense</span>
                <strong>PKR {{ number_format($totalExpense, 2) }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>{{ $netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}</span>
                <strong>PKR {{ number_format(abs($netProfit), 2) }}</strong>
            </div>
        </div>

        <div class="kes-report-two-grid">
            <div>
                <h3>Income</h3>

                <div class="kes-report-table-wrap">
                    <table class="kes-report-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Account</th>
                                <th class="right">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($incomeRows as $row)
                                <tr>
                                    <td>{{ $row['code'] }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="right">PKR {{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No income found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="2">Total Income</th>
                                <th class="right">PKR {{ number_format($totalIncome, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div>
                <h3>Expenses</h3>

                <div class="kes-report-table-wrap">
                    <table class="kes-report-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Account</th>
                                <th class="right">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($expenseRows as $row)
                                <tr>
                                    <td>{{ $row['code'] }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="right">PKR {{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No expense found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="2">Total Expenses</th>
                                <th class="right">PKR {{ number_format($totalExpense, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="kes-report-net {{ $netProfit >= 0 ? 'profit' : 'loss' }}">
            <span>{{ $netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}</span>
            <strong>PKR {{ number_format(abs($netProfit), 2) }}</strong>
        </div>
    </div>
</x-filament-panels::page>