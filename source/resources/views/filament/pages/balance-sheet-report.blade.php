<x-filament-panels::page>
    @php
        $assetRows = $this->assetRows();
        $liabilityRows = $this->liabilityRows();
        $equityRows = $this->equityRows();

        $totalAssets = $assetRows->sum('amount');
        $totalLiabilities = $liabilityRows->sum('amount');
        $totalEquity = $equityRows->sum('amount');
        $totalLiabilityEquity = $totalLiabilities + $totalEquity;
    @endphp

    <div class="kes-report-card">
        <div class="kes-report-head">
            <div>
                <h2>Balance Sheet Report</h2>
                <p>Assets, liabilities and equity position.</p>
            </div>

            <button onclick="window.print()">Print</button>
        </div>

        <form method="GET" class="kes-report-filters">
            <input type="date" name="to_date" value="{{ request('to_date') }}">
            <button type="submit">Apply</button>
            <a href="{{ url()->current() }}">Reset</a>
        </form>

        <div class="kes-report-summary-grid">
            <div class="kes-report-summary-card">
                <span>Total Assets</span>
                <strong>PKR {{ number_format($totalAssets, 2) }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Total Liabilities</span>
                <strong>PKR {{ number_format($totalLiabilities, 2) }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Total Equity</span>
                <strong>PKR {{ number_format($totalEquity, 2) }}</strong>
            </div>
        </div>

        <div class="kes-report-two-grid">
            <div>
                <h3>Assets</h3>

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
                            @forelse ($assetRows as $row)
                                <tr>
                                    <td>{{ $row['code'] }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="right">PKR {{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No assets found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="2">Total Assets</th>
                                <th class="right">PKR {{ number_format($totalAssets, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div>
                <h3>Liabilities & Equity</h3>

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
                            <tr>
                                <th colspan="3">Liabilities</th>
                            </tr>

                            @forelse ($liabilityRows as $row)
                                <tr>
                                    <td>{{ $row['code'] }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="right">PKR {{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No liabilities found.</td>
                                </tr>
                            @endforelse

                            <tr>
                                <th colspan="3">Equity</th>
                            </tr>

                            @forelse ($equityRows as $row)
                                <tr>
                                    <td>{{ $row['code'] }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="right">PKR {{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No equity found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="2">Total Liabilities + Equity</th>
                                <th class="right">PKR {{ number_format($totalLiabilityEquity, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>