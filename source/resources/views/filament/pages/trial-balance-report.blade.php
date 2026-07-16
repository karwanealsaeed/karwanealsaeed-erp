<x-filament-panels::page>
    @php
        $rows = $this->rows();
        $totalDebit = $rows->sum('debit');
        $totalCredit = $rows->sum('credit');
    @endphp

    <div class="kes-report-card">
        <div class="kes-report-head">
            <div>
                <h2>Trial Balance Report</h2>
                <p>Debit and credit balance of all accounts.</p>
            </div>

            <button onclick="window.print()">Print</button>
        </div>

        <div class="kes-report-table-wrap">
            <table class="kes-report-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Account</th>
                        <th>Type</th>
                        <th class="right">Debit</th>
                        <th class="right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['code'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['type'] }}</td>
                            <td class="right">PKR {{ number_format($row['debit'], 2) }}</td>
                            <td class="right">PKR {{ number_format($row['credit'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th class="right">PKR {{ number_format($totalDebit, 2) }}</th>
                        <th class="right">PKR {{ number_format($totalCredit, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament-panels::page>